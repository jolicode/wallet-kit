<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Apple;

use Jolicode\WalletKit\Api\Apple\ApplePassPackager;
use Jolicode\WalletKit\Api\Credentials\AppleCredentials;
use Jolicode\WalletKit\Exception\Api\PackagingException;
use Jolicode\WalletKit\Pass\Apple\Model\Pass;
use Jolicode\WalletKit\Pass\Apple\Model\PassStructure;
use Jolicode\WalletKit\Pass\Apple\Model\PassTypeEnum;
use Jolicode\WalletKit\Tests\Builder\BuilderTestSerializerFactory;
use PHPUnit\Framework\TestCase;

final class ApplePassPackagerTest extends TestCase
{
    private string $p12Path;
    private string $wwdrPath;

    protected function setUp(): void
    {
        // Generate a self-signed CA (acting as WWDR)
        $caKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => \OPENSSL_KEYTYPE_RSA]);
        self::assertNotFalse($caKey);
        $caCsr = openssl_csr_new(['CN' => 'Test WWDR CA'], $caKey);
        self::assertNotFalse($caCsr);
        $caCert = openssl_csr_sign($caCsr, null, $caKey, 365);
        self::assertNotFalse($caCert);

        // Export CA cert to PEM, then convert to DER for the WWDR path
        $caPem = '';
        openssl_x509_export($caCert, $caPem);
        $this->wwdrPath = tempnam(sys_get_temp_dir(), 'wallet_kit_wwdr_') ?: '';
        // Write PEM format (openssl_pkcs7_sign accepts PEM CA certs)
        file_put_contents($this->wwdrPath, $caPem);

        // Generate a leaf certificate signed by the CA
        $leafKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => \OPENSSL_KEYTYPE_RSA]);
        self::assertNotFalse($leafKey);
        $leafCsr = openssl_csr_new(['CN' => 'Pass Type ID: pass.com.example'], $leafKey);
        self::assertNotFalse($leafCsr);
        $leafCert = openssl_csr_sign($leafCsr, $caCert, $caKey, 365);
        self::assertNotFalse($leafCert);

        // Export to P12
        $this->p12Path = tempnam(sys_get_temp_dir(), 'wallet_kit_p12_') ?: '';
        openssl_pkcs12_export_to_file($leafCert, $this->p12Path, $leafKey, 'test');
    }

    protected function tearDown(): void
    {
        @unlink($this->p12Path);
        @unlink($this->wwdrPath);
    }

    private function createPass(): Pass
    {
        return new Pass(
            description: 'Test Pass',
            organizationName: 'Test Org',
            teamIdentifier: 'TEAM123',
            passTypeIdentifier: 'pass.com.example',
            formatVersion: 1,
            serialNumber: 'SERIAL-001',
            passType: PassTypeEnum::GENERIC,
            structure: new PassStructure(),
        );
    }

    private function createPackager(?string $wwdrPath = null): ApplePassPackager
    {
        $serializer = BuilderTestSerializerFactory::create();
        $credentials = new AppleCredentials(
            certificatePath: $this->p12Path,
            certificatePassword: 'test',
            wwdrCertificatePath: $wwdrPath ?? $this->wwdrPath,
        );

        return new ApplePassPackager($serializer, $credentials);
    }

    public function testPackageProducesValidZip(): void
    {
        $packager = $this->createPackager();
        $pass = $this->createPass();

        $iconPath = $this->createTempImage('icon-content');

        try {
            $pkpass = $packager->package($pass, ['icon.png' => $iconPath]);

            self::assertNotEmpty($pkpass);

            // Verify it's a valid ZIP
            $tmpZip = tempnam(sys_get_temp_dir(), 'wallet_kit_test_zip_');
            self::assertNotFalse($tmpZip);
            file_put_contents($tmpZip, $pkpass);

            $zip = new \ZipArchive();
            self::assertTrue($zip->open($tmpZip));

            // Check expected files exist
            self::assertNotFalse($zip->locateName('pass.json'));
            self::assertNotFalse($zip->locateName('manifest.json'));
            self::assertNotFalse($zip->locateName('signature'));
            self::assertNotFalse($zip->locateName('icon.png'));

            // Verify pass.json is valid JSON
            $passJson = $zip->getFromName('pass.json');
            self::assertNotFalse($passJson);
            $passData = json_decode($passJson, true, 512, \JSON_THROW_ON_ERROR);
            self::assertSame('Test Pass', $passData['description']);
            self::assertSame('SERIAL-001', $passData['serialNumber']);

            // Verify manifest.json contains SHA1 hashes
            $manifestJson = $zip->getFromName('manifest.json');
            self::assertNotFalse($manifestJson);
            $manifest = json_decode($manifestJson, true, 512, \JSON_THROW_ON_ERROR);
            self::assertArrayHasKey('pass.json', $manifest);
            self::assertArrayHasKey('icon.png', $manifest);
            self::assertSame(sha1($passJson), $manifest['pass.json']);

            // Verify icon content
            $iconContent = $zip->getFromName('icon.png');
            self::assertSame('icon-content', $iconContent);

            $zip->close();
            @unlink($tmpZip);
        } finally {
            @unlink($iconPath);
        }
    }

    public function testPackageWithLocalizations(): void
    {
        $packager = $this->createPackager();
        $pass = $this->createPass();

        $iconPath = $this->createTempImage('icon');

        try {
            $pkpass = $packager->package($pass, ['icon.png' => $iconPath], [
                'en' => ['greeting' => 'Hello', 'store_name' => 'My Store'],
                'fr' => ['greeting' => 'Bonjour', 'store_name' => 'Mon Magasin'],
            ]);

            $tmpZip = tempnam(sys_get_temp_dir(), 'wallet_kit_test_zip_');
            self::assertNotFalse($tmpZip);
            file_put_contents($tmpZip, $pkpass);

            $zip = new \ZipArchive();
            self::assertTrue($zip->open($tmpZip));

            // Check localization files exist
            self::assertNotFalse($zip->locateName('en.lproj/pass.strings'));
            self::assertNotFalse($zip->locateName('fr.lproj/pass.strings'));

            // Verify pass.strings format
            $enStrings = $zip->getFromName('en.lproj/pass.strings');
            self::assertNotFalse($enStrings);
            self::assertStringContainsString('"greeting" = "Hello";', $enStrings);
            self::assertStringContainsString('"store_name" = "My Store";', $enStrings);

            $frStrings = $zip->getFromName('fr.lproj/pass.strings');
            self::assertNotFalse($frStrings);
            self::assertStringContainsString('"greeting" = "Bonjour";', $frStrings);
            self::assertStringContainsString('"store_name" = "Mon Magasin";', $frStrings);

            // Verify localization files are in the manifest
            $manifestJson = $zip->getFromName('manifest.json');
            self::assertNotFalse($manifestJson);
            $manifest = json_decode($manifestJson, true, 512, \JSON_THROW_ON_ERROR);
            self::assertArrayHasKey('en.lproj/pass.strings', $manifest);
            self::assertArrayHasKey('fr.lproj/pass.strings', $manifest);

            $zip->close();
            @unlink($tmpZip);
        } finally {
            @unlink($iconPath);
        }
    }

    public function testPackageWithLocalizedImages(): void
    {
        $packager = $this->createPackager();
        $pass = $this->createPass();

        $iconPath = $this->createTempImage('icon');
        $frLogoPath = $this->createTempImage('fr-logo');

        try {
            $pkpass = $packager->package($pass, [
                'icon.png' => $iconPath,
                'fr.lproj/logo.png' => $frLogoPath,
            ]);

            $tmpZip = tempnam(sys_get_temp_dir(), 'wallet_kit_test_zip_');
            self::assertNotFalse($tmpZip);
            file_put_contents($tmpZip, $pkpass);

            $zip = new \ZipArchive();
            self::assertTrue($zip->open($tmpZip));

            self::assertNotFalse($zip->locateName('fr.lproj/logo.png'));
            self::assertSame('fr-logo', $zip->getFromName('fr.lproj/logo.png'));

            // Verify localized image is in manifest
            $manifestJson = $zip->getFromName('manifest.json');
            self::assertNotFalse($manifestJson);
            $manifest = json_decode($manifestJson, true, 512, \JSON_THROW_ON_ERROR);
            self::assertArrayHasKey('fr.lproj/logo.png', $manifest);

            $zip->close();
            @unlink($tmpZip);
        } finally {
            @unlink($iconPath);
            @unlink($frLogoPath);
        }
    }

    public function testPackageThrowsOnInvalidCertificate(): void
    {
        $serializer = BuilderTestSerializerFactory::create();
        $badP12Path = tempnam(sys_get_temp_dir(), 'wallet_kit_bad_p12_');
        self::assertNotFalse($badP12Path);
        file_put_contents($badP12Path, 'not-a-valid-p12');

        $credentials = new AppleCredentials(
            certificatePath: $badP12Path,
            certificatePassword: 'wrong',
            wwdrCertificatePath: $this->wwdrPath,
        );

        $packager = new ApplePassPackager($serializer, $credentials);

        try {
            $this->expectException(PackagingException::class);
            $this->expectExceptionMessage('Unable to parse P12 certificate');

            $iconPath = $this->createTempImage('icon');

            try {
                $packager->package($this->createPass(), ['icon.png' => $iconPath]);
            } finally {
                @unlink($iconPath);
            }
        } finally {
            @unlink($badP12Path);
        }
    }

    public function testPackageThrowsWhenIconMissing(): void
    {
        $packager = $this->createPackager();

        $this->expectException(PackagingException::class);
        $this->expectExceptionMessage('icon.png');

        $packager->package($this->createPass(), []);
    }

    public function testPassStringsEscapesSpecialCharacters(): void
    {
        $packager = $this->createPackager();
        $iconPath = $this->createTempImage('icon');

        try {
            $pkpass = $packager->package($this->createPass(), ['icon.png' => $iconPath], [
                'en' => [
                    'quote' => 'He said "hi"',
                    'back\\slash' => 'value\\with\\backslashes',
                    'newline' => "line1\nline2",
                    'tab' => "a\tb",
                    'cr' => "x\ry",
                ],
            ]);

            $tmpZip = tempnam(sys_get_temp_dir(), 'wallet_kit_test_zip_');
            self::assertNotFalse($tmpZip);
            file_put_contents($tmpZip, $pkpass);

            $zip = new \ZipArchive();
            self::assertTrue($zip->open($tmpZip));
            $strings = $zip->getFromName('en.lproj/pass.strings');
            self::assertNotFalse($strings);

            self::assertStringContainsString('"quote" = "He said \\"hi\\"";', $strings);
            self::assertStringContainsString('"back\\\\slash" = "value\\\\with\\\\backslashes";', $strings);
            self::assertStringContainsString('"newline" = "line1\\nline2";', $strings);
            self::assertStringContainsString('"tab" = "a\\tb";', $strings);
            self::assertStringContainsString('"cr" = "x\\ry";', $strings);
            self::assertStringEndsWith("\n", $strings);

            $zip->close();
            @unlink($tmpZip);
        } finally {
            @unlink($iconPath);
        }
    }

    public function testPackageThrowsOnUnreadableImage(): void
    {
        $packager = $this->createPackager();

        $this->expectException(PackagingException::class);
        $this->expectExceptionMessage('Unable to read image');

        $packager->package($this->createPass(), ['icon.png' => '/nonexistent/path/icon.png']);
    }

    public function testManifestContainsSha1Hashes(): void
    {
        $packager = $this->createPackager();
        $pass = $this->createPass();

        $iconPath = $this->createTempImage('test-icon-content');

        try {
            $pkpass = $packager->package($pass, ['icon.png' => $iconPath]);

            $tmpZip = tempnam(sys_get_temp_dir(), 'wallet_kit_test_zip_');
            self::assertNotFalse($tmpZip);
            file_put_contents($tmpZip, $pkpass);

            $zip = new \ZipArchive();
            self::assertTrue($zip->open($tmpZip));

            $manifestJson = $zip->getFromName('manifest.json');
            self::assertNotFalse($manifestJson);
            $manifest = json_decode($manifestJson, true, 512, \JSON_THROW_ON_ERROR);

            // Verify icon SHA1 matches the actual content
            $iconContent = $zip->getFromName('icon.png');
            self::assertNotFalse($iconContent);
            self::assertSame(sha1($iconContent), $manifest['icon.png']);

            // Verify pass.json SHA1 matches
            $passJson = $zip->getFromName('pass.json');
            self::assertNotFalse($passJson);
            self::assertSame(sha1($passJson), $manifest['pass.json']);

            $zip->close();
            @unlink($tmpZip);
        } finally {
            @unlink($iconPath);
        }
    }

    private function createTempImage(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'wallet_kit_img_');
        self::assertNotFalse($path);
        file_put_contents($path, $content);

        return $path;
    }

    /**
     * The critical end-to-end guarantee: the "signature" ZIP entry is a detached
     * CMS that verifies against manifest.json, using the real certificates from
     * the packaging flow (leaf from the P12, CA passed as trust anchor).
     */
    public function testPackageSignatureIsVerifiableOverManifest(): void
    {
        $packager = $this->createPackager();
        $pass = $this->createPass();

        $iconPath = $this->createTempImage('roundtrip-icon');

        try {
            $pkpass = $packager->package($pass, ['icon.png' => $iconPath]);

            $tmpZip = tempnam(sys_get_temp_dir(), 'wallet_kit_rt_zip_');
            self::assertNotFalse($tmpZip);
            file_put_contents($tmpZip, $pkpass);

            $zip = new \ZipArchive();
            self::assertTrue($zip->open($tmpZip));

            $manifestFile = tempnam(sys_get_temp_dir(), 'wallet_kit_rt_manifest_');
            $signatureFile = tempnam(sys_get_temp_dir(), 'wallet_kit_rt_sig_');
            self::assertNotFalse($manifestFile);
            self::assertNotFalse($signatureFile);

            file_put_contents($manifestFile, $zip->getFromName('manifest.json'));
            file_put_contents($signatureFile, $zip->getFromName('signature'));
            $zip->close();

            // === Round-trip: verify the PKCS#7 signature against the manifest ===
            // PHP's openssl_cms_verify() mishandles detached DER input on some
            // builds (content bytes get EOL-normalized), so use the openssl CLI —
            // the exact same binary the packaging pipeline relies on.
            if (false === (\shell_exec('command -v openssl'))) {
                self::markTestSkipped('openssl CLI not available.');
            }

            /*
             * Full PKI check: chain verification against the CA used as WWDR.
             * (PKCS#7_DETACHED was requested in the packager, DER-in form.)
             */
            $cmd = \sprintf(
                'openssl cms -verify -binary -inform DER -in %s -content %s -CAfile %s 2>&1',
                \escapeshellarg($signatureFile),
                \escapeshellarg($manifestFile),
                \escapeshellarg($this->wwdrPath),
            );
            \exec($cmd, $outLines, $exitCode);

            self::assertSame(
                0,
                $exitCode,
                'PKCS#7 signature must verify against manifest.json (openssl output: ' . \implode("\n", $outLines) . ')',
            );

            @unlink($manifestFile);
            @unlink($signatureFile);
            @unlink($tmpZip);
        } finally {
            @unlink($iconPath);
        }
    }

    public function testRejectsReservedImageFilename(): void
    {
        $packager = $this->createPackager();
        $iconPath = $this->createTempImage('icon');
        $evilPath = $this->createTempImage('evil');

        try {
            $this->expectException(PackagingException::class);
            $this->expectExceptionMessage('Reserved or unsafe file name');

            $packager->package($this->createPass(), [
                'icon.png' => $iconPath,
                'pass.json' => $evilPath,
            ]);
        } finally {
            @unlink($iconPath);
            @unlink($evilPath);
        }
    }

    public function testRejectsPathTraversalInImageFilename(): void
    {
        $packager = $this->createPackager();
        $iconPath = $this->createTempImage('icon');
        $evilPath = $this->createTempImage('evil');

        try {
            $this->expectException(PackagingException::class);
            $this->expectExceptionMessage('Reserved or unsafe file name');

            $packager->package($this->createPass(), [
                'icon.png' => $iconPath,
                '../escape.png' => $evilPath,
            ]);
        } finally {
            @unlink($iconPath);
            @unlink($evilPath);
        }
    }

    /**
     * The shipped default resource (DER .cer) must convert to PEM and sign fine,
     * and no temporary file may be left behind after the packager is destroyed.
     */
    public function testBuiltInWwdrResourceConvertsAndCleansUp(): void
    {
        $tmpDir = sys_get_temp_dir();
        $expectedLeftovers = \array_values(\glob($tmpDir . '/wallet_kit_wwdr_pem_*') ?: []);

        $serializer = BuilderTestSerializerFactory::create();
        $credentials = new AppleCredentials(
            certificatePath: $this->p12Path,
            certificatePassword: 'test',
            wwdrCertificatePath: null, // use built-in AppleWWDRCAG4.cer resource
        );

        $packager = new ApplePassPackager($serializer, $credentials);
        $iconPath = $this->createTempImage('icon');

        try {
            $pkpass = $packager->package($this->createPass(), ['icon.png' => $iconPath]);
            self::assertNotEmpty($pkpass);
        } finally {
            @unlink($iconPath);
        }

        unset($packager); // trigger __destruct: temp PEM files must be cleaned up

        $leftovers = \array_values(\array_diff(\glob($tmpDir . '/wallet_kit_wwdr_pem_*') ?: [], $expectedLeftovers));
        self::assertEmpty($leftovers, 'WWDR temp PEM file was not cleaned up: ' . \print_r($leftovers, true));
    }
}
