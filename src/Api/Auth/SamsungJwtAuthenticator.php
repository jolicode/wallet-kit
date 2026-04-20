<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Auth;

use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Exception\Api\AuthenticationException;
use Jolicode\WalletKit\Exception\Api\MissingExtensionException;

final class SamsungJwtAuthenticator
{
    private ?CachedToken $cachedToken = null;

    public function __construct(
        private readonly SamsungCredentials $credentials,
    ) {
        if (!\extension_loaded('openssl')) {
            throw new MissingExtensionException('The "openssl" PHP extension is required for Samsung JWT authentication.');
        }
    }

    public function getToken(): TokenInterface
    {
        if (null !== $this->cachedToken && !$this->cachedToken->isExpired()) {
            return $this->cachedToken;
        }

        $this->cachedToken = $this->createToken();

        return $this->cachedToken;
    }

    private function createToken(): CachedToken
    {
        $now = time();

        $header = self::base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], \JSON_THROW_ON_ERROR));
        $claims = self::base64UrlEncode(json_encode([
            'iss' => $this->credentials->partnerId,
            'iat' => $now,
            'exp' => $now + 3600,
        ], \JSON_THROW_ON_ERROR));

        $signingInput = $header . '.' . $claims;

        $privateKeyContent = @file_get_contents($this->credentials->privateKeyPath);

        if (false === $privateKeyContent) {
            throw new AuthenticationException(\sprintf('Unable to read Samsung private key at "%s".', $this->credentials->privateKeyPath));
        }

        $key = openssl_pkey_get_private($privateKeyContent);

        if (false === $key) {
            throw new AuthenticationException('Unable to parse Samsung private key.');
        }

        $signature = '';

        if (!openssl_sign($signingInput, $signature, $key, \OPENSSL_ALGO_SHA256)) {
            throw new AuthenticationException(\sprintf('Failed to sign Samsung JWT: %s', openssl_error_string() ?: 'unknown error'));
        }

        $jwt = $signingInput . '.' . self::base64UrlEncode($signature);

        return new CachedToken(
            $jwt,
            new \DateTimeImmutable(\sprintf('+%d seconds', 3600 - 60)),
        );
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
