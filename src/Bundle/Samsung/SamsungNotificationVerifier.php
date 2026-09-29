<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Samsung;

use Jolicode\WalletKit\Api\Auth\Jwt;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;

/**
 * Verifies inbound "Partner API" calls from Samsung (state-change callbacks).
 *
 * Samsung signs these requests with the Authorization JWS (RS256, per the
 * "REST API Authorization Token" spec) using Samsung's own private key; the
 * public certificate for verification is distributed through the Partner site.
 *
 * When no verification key is configured, requests are accepted and a warning
 * is logged — this is fail-open by design, but loudly documented (docs/bundle.md):
 * configure "samsung.public_key_path" to get fail-closed behavior.
 */
final class SamsungNotificationVerifier
{
    private const MAX_CLOCK_SKEW_SECONDS = 300;

    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly ?string $publicKeyPath,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function isVerificationEnabled(): bool
    {
        return null !== $this->publicKeyPath;
    }

    /**
     * @return bool true when the request is verified (or verification is disabled).
     */
    public function verify(Request $request): bool
    {
        if (null === $this->publicKeyPath) {
            $this->logger->notice(
                'Samsung callback accepted without signature verification: no public key configured. Set wallet_kit.samsung.public_key_path.',
            );

            return true;
        }
        if (!$this->hasOpenSsl()) {
            throw new \LogicException('The openssl extension is required to verify Samsung notification signatures.');
        }

        $publicKey = self::loadPublicKey($this->publicKeyPath);

        if (null === $publicKey) {
            throw new \LogicException(\sprintf('Could not load Samsung public key from "%s".', $this->publicKeyPath));
        }

        $header = $request->headers->get('Authorization') ?? '';
        $jws = trim(\str_starts_with($header, 'Bearer ') ? substr($header, 7) : $header);
        $parts = \explode('.', $jws);

        if (3 !== \count($parts)) {
            return false;
        }
        [$headB64, $payloadB64, $sigB64] = $parts;
        $signature = \base64_decode($sigB64, true);

        if (false === $signature) {
            return false;
        }

        $verified = \openssl_verify(
            $headB64 . '.' . $payloadB64,
            $signature,
            $publicKey,
            \OPENSSL_ALGO_SHA256,
        );

        if (1 !== $verified) {
            return false;
        }

        $payload = json_decode(Jwt::base64UrlDecode($payloadB64), true);

        if (!\is_array($payload)) {
            return false;
        }

        // Anti-replay: the token must carry a fresh utc (epoch milliseconds).
        if (!\array_key_exists('utc', $payload) || !\is_int($payload['utc'])) {
            return false;
        }

        $ageSeconds = (\time() * 1000 - $payload['utc']) / 1000;

        return \abs($ageSeconds) <= self::MAX_CLOCK_SKEW_SECONDS;
    }

    private function hasOpenSsl(): bool
    {
        return \extension_loaded('openssl');
    }

    private static function loadPublicKey(string $path): \OpenSSLAsymmetricKey|false
    {
        $content = \file_get_contents($path);

        if (false === $content) {
            return false;
        }

        return \openssl_pkey_get_public($content);
    }
}
