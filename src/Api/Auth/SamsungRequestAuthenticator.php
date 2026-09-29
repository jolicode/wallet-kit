<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Auth;

use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Exception\Api\AuthenticationException;
use Jolicode\WalletKit\Exception\Api\MissingExtensionException;

/**
 * Builds Samsung's per-request "Authorization Token" (REST API Authorization Token spec).
 *
 * Unlike Apple APNs / Google OAuth2 bearer tokens, the Samsung token is bound to a
 * single request: the JWS payload carries the API method + path and must be re-minted
 * for every call (no caching). The header carries the certificateId, partnerId and a
 * utc timestamp (epoch milliseconds) used by Samsung for expiry / anti-replay checks.
 *
 * @see https://developer.samsung.com/wallet/securityauthentication/restapiauthorizationtoken.html
 */
final class SamsungRequestAuthenticator
{
    private const TOKEN_VERSION = '3';

    private readonly \OpenSSLAsymmetricKey $privateKey;

    public function __construct(
        private readonly SamsungCredentials $credentials,
    ) {
        if (!\extension_loaded('openssl')) {
            throw new MissingExtensionException('The "openssl" PHP extension is required for Samsung authentication.');
        }

        $this->privateKey = self::loadPrivateKey($credentials->privateKeyPath);
    }

    /**
     * @param string $method HTTP method of the request, e.g. "GET"/"POST".
     * @param string $path   API path only (excluding scheme/host/query),
     *                       e.g. "/partner/v1/card/template/{cardId}".
     *
     * @return string the fresh JWS, ready to be sent as "Authorization: Bearer …"
     */
    public function createAuthorizationToken(string $method, string $path): string
    {
        if ('' === $path || '/' !== $path[0]) {
            throw new \ValueError('The Authorization Token payload must bind an API path starting with "/".');
        }

        $nowMs = (int) (microtime(true) * 1000);

        $header = Jwt::base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'cty' => 'AUTH',
            'ver' => self::TOKEN_VERSION,
            'certificateId' => $this->credentials->certificateId,
            'partnerId' => $this->credentials->partnerId,
            'utc' => $nowMs,
        ], \JSON_THROW_ON_ERROR));

        $payload = Jwt::base64UrlEncode(json_encode([
            'API' => [
                'method' => strtoupper($method),
                'path' => $path,
            ],
        ], \JSON_THROW_ON_ERROR));

        return $this->sign($header, $payload);
    }

    private function sign(string $header, string $payload): string
    {
        $signingInput = $header . '.' . $payload;
        $signature = '';

        if (!openssl_sign($signingInput, $signature, $this->privateKey, \OPENSSL_ALGO_SHA256)) {
            throw new AuthenticationException(\sprintf('Failed to sign Samsung authorization token: %s', openssl_error_string() ?: 'unknown error'));
        }

        return $signingInput . '.' . Jwt::base64UrlEncode($signature);
    }

    private static function loadPrivateKey(string $path): \OpenSSLAsymmetricKey
    {
        $content = @file_get_contents($path);

        if (false === $content) {
            throw new AuthenticationException(\sprintf('Unable to read Samsung private key at "%s".', $path));
        }

        $key = openssl_pkey_get_private($content);

        if (false === $key) {
            throw new AuthenticationException('Unable to parse Samsung private key.');
        }

        return $key;
    }
}
