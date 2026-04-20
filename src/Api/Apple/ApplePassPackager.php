<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Apple;

use Jolicode\WalletKit\Api\Credentials\AppleCredentials;
use Jolicode\WalletKit\Exception\Api\MissingExtensionException;
use Jolicode\WalletKit\Exception\Api\PackagingException;
use Jolicode\WalletKit\Pass\Apple\Model\Pass;
use Symfony\Component\Serializer\SerializerInterface;

final class ApplePassPackager
{
    private readonly string $wwdrCertificatePath;

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly AppleCredentials $credentials,
    ) {
        if (!\extension_loaded('openssl')) {
            throw new MissingExtensionException('The "openssl" PHP extension is required for Apple .pkpass signing.');
        }

        if (!\extension_loaded('zip')) {
            throw new MissingExtensionException('The "zip" PHP extension is required for Apple .pkpass packaging.');
        }

        $this->wwdrCertificatePath = $credentials->wwdrCertificatePath ?? __DIR__ . '/Resources/AppleWWDRCAG4.cer';
    }

    /**
     * @param Pass                                 $pass          The Apple pass model
     * @param array<string, string>                $images        Filename => local path or URL (e.g. ['icon.png' => '/path/to/icon.png'])
     * @param array<string, array<string, string>> $localizations Locale => [key => value] for .lproj/pass.strings
     *
     * @return string Raw .pkpass binary (ZIP)
     */
    public function package(Pass $pass, array $images, array $localizations = []): string
    {
        // 1. Serialize pass to JSON
        $passJson = $this->serializer->serialize($pass, 'json');

        // 2. Collect all files: pass.json + resolved images
        /** @var array<string, string> $files filename => binary content */
        $files = ['pass.json' => $passJson];

        foreach ($images as $filename => $pathOrUrl) {
            $content = @file_get_contents($pathOrUrl);

            if (false === $content) {
                throw new PackagingException(\sprintf('Unable to read image "%s" from "%s".', $filename, $pathOrUrl));
            }

            $files[$filename] = $content;
        }

        // 3. Generate .lproj/pass.strings for each locale
        foreach ($localizations as $locale => $strings) {
            $stringsContent = $this->buildPassStrings($strings);
            $files[\sprintf('%s.lproj/pass.strings', $locale)] = $stringsContent;
        }

        // 4. Compute SHA1 hash of each file -> manifest.json
        $manifest = [];
        foreach ($files as $filename => $content) {
            $manifest[$filename] = sha1($content);
        }
        $manifestJson = json_encode($manifest, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR);
        $files['manifest.json'] = $manifestJson;

        // 5. Sign manifest.json
        $signature = $this->signManifest($manifestJson);
        $files['signature'] = $signature;

        // 6. Create ZIP archive
        return $this->createZip($files);
    }

    /**
     * @param array<string, string> $strings
     */
    private function buildPassStrings(array $strings): string
    {
        $lines = [];
        foreach ($strings as $key => $value) {
            $escapedKey = str_replace(['"', '\\'], ['\\"', '\\\\'], $key);
            $escapedValue = str_replace(['"', '\\'], ['\\"', '\\\\'], $value);
            $lines[] = \sprintf('"%s" = "%s";', $escapedKey, $escapedValue);
        }

        return implode("\n", $lines);
    }

    private function signManifest(string $manifestJson): string
    {
        // Load P12 certificate
        $p12Content = @file_get_contents($this->credentials->certificatePath);

        if (false === $p12Content) {
            throw new PackagingException(\sprintf('Unable to read P12 certificate at "%s".', $this->credentials->certificatePath));
        }

        $certs = [];
        if (!openssl_pkcs12_read($p12Content, $certs, $this->credentials->certificatePassword)) {
            throw new PackagingException(\sprintf('Unable to parse P12 certificate at "%s". Check the password.', $this->credentials->certificatePath));
        }

        $certResource = openssl_x509_read($certs['cert']);

        if (false === $certResource) {
            throw new PackagingException('Unable to read the certificate from the P12 file.');
        }

        $privateKey = openssl_pkey_get_private($certs['pkey']);

        if (false === $privateKey) {
            throw new PackagingException('Unable to read the private key from the P12 file.');
        }

        // Write manifest to temp file for openssl_pkcs7_sign
        $manifestTmp = tempnam(sys_get_temp_dir(), 'wallet_kit_manifest_');
        $signatureTmp = tempnam(sys_get_temp_dir(), 'wallet_kit_signature_');

        if (false === $manifestTmp || false === $signatureTmp) {
            throw new PackagingException('Unable to create temporary files for signing.');
        }

        try {
            file_put_contents($manifestTmp, $manifestJson);

            $signed = openssl_pkcs7_sign(
                $manifestTmp,
                $signatureTmp,
                $certResource,
                $privateKey,
                [],
                \PKCS7_BINARY | \PKCS7_DETACHED | \PKCS7_NOATTR,
                $this->wwdrCertificatePath,
            );

            if (!$signed) {
                throw new PackagingException(\sprintf('Failed to sign manifest: %s', openssl_error_string() ?: 'unknown error'));
            }

            $signatureContent = file_get_contents($signatureTmp);

            if (false === $signatureContent) {
                throw new PackagingException('Unable to read the generated signature.');
            }

            // openssl_pkcs7_sign outputs S/MIME format.
            // Extract the base64-encoded PKCS7 signature from the last MIME part.
            $smime = $signatureContent;

            // Find the signature block after Content-Transfer-Encoding: base64
            $separator = "\n\n";
            $lastPartStart = strrpos($smime, $separator);

            if (false === $lastPartStart) {
                throw new PackagingException('Unable to extract DER signature from S/MIME output.');
            }

            // Extract the base64 content (last block before the closing boundary)
            $base64Block = substr($smime, $lastPartStart + \strlen($separator));

            // Remove the trailing MIME boundary line (e.g. "------BOUNDARY--\n")
            $base64Block = preg_replace('/\r?\n------[^\n]+--\s*$/', '', $base64Block);

            if (null === $base64Block) {
                throw new PackagingException('Unable to clean DER signature.');
            }

            $der = base64_decode(trim($base64Block), true);

            if (false === $der) {
                throw new PackagingException('Unable to decode DER signature.');
            }

            return $der;
        } finally {
            @unlink($manifestTmp);
            @unlink($signatureTmp);
        }
    }

    /**
     * @param array<string, string> $files filename => content
     */
    private function createZip(array $files): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'wallet_kit_pkpass_');

        if (false === $tmpFile) {
            throw new PackagingException('Unable to create temporary file for ZIP archive.');
        }

        try {
            $zip = new \ZipArchive();
            $result = $zip->open($tmpFile, \ZipArchive::OVERWRITE);

            if (true !== $result) {
                throw new PackagingException(\sprintf('Unable to create ZIP archive (error code: %d).', $result));
            }

            foreach ($files as $filename => $content) {
                $zip->addFromString($filename, $content);
            }

            $zip->close();

            $zipContent = file_get_contents($tmpFile);

            if (false === $zipContent) {
                throw new PackagingException('Unable to read the generated ZIP archive.');
            }

            return $zipContent;
        } finally {
            @unlink($tmpFile);
        }
    }
}
