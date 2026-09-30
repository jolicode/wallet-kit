<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Samsung;

use Jolicode\WalletKit\Api\Auth\Jwt;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Exception\Api\MissingExtensionException;

/**
 * Secure-tokens card payloads for Samsung Wallet (the "cdata" / "ctemplate" body values).
 *
 * Samsung's token spec: content type ("CARD", "NOTIFICATION", …) goes in the JWS header
 * (ver 3) and the payload is signed with the partner private key. The full spec expects
 * JWS-wrapped JWE (payload encrypted to Samsung's RSA public key, RSA-OAEP / A256GCM);
 * PHP's openssl binding cannot produce RSA-OAEP-SHA256 JWEs, so this helper ships the
 * SIGNED (JWS) form, which the Samsung web flows accept for partner-signed content.
 * Subscribe to Samsung's security update notes before relying on this for JWE-only flows.
 *
 * @see https://developer.samsung.com/wallet/references/security.html
 */
final class SamsungCardTokenizer
{
    private const TOKEN_VERSION = '3';

    private readonly ?\OpenSSLAsymmetricKey $privateKey;

    public function __construct(
        private readonly SamsungCredentials $credentials,
    ) {
        if (!\extension_loaded('openssl')) {
            throw new MissingExtensionException('The "openssl" PHP extension is required for Samsung card data tokens.');
        }

        $key = @file_get_contents($credentials->privateKeyPath);

        if (false === $key) {
            throw new \RuntimeException(\sprintf('Unable to read Samsung private key at "%s".', $credentials->privateKeyPath));
        }

        $privateKey = openssl_pkey_get_private($key);
        $this->privateKey = false === $privateKey ? null : $privateKey;
    }

    /**
     * Signs a complete JWT payload. Callers choose the wrapping keys exactly as
     * their card flow expects (e.g. {"card": …}, {"cardTemplate": …}).
     *
     * @param string               $contentType JWS header "cty" value, e.g. "CARD" or "NOTIFICATION".
     * @param array<string, mixed> $payload     the full JWT payload to sign
     */
    public function tokenize(string $contentType, array $payload): string
    {
        if (null === $this->privateKey) {
            throw new \RuntimeException('Unable to parse Samsung private key.');
        }

        $nowMs = (int) (microtime(true) * 1000);

        $header = Jwt::base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'cty' => strtoupper($contentType),
            'ver' => self::TOKEN_VERSION,
            'certificateId' => $this->credentials->certificateId,
            'partnerId' => $this->credentials->partnerId,
            'utc' => $nowMs,
        ], \JSON_THROW_ON_ERROR));

        $payloadJson = Jwt::base64UrlEncode(json_encode($payload, \JSON_THROW_ON_ERROR));

        $signingInput = $header . '.' . $payloadJson;
        $signature = '';

        if (!openssl_sign($signingInput, $signature, $this->privateKey, \OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException(\sprintf('Failed to sign Samsung card data token: %s', openssl_error_string() ?: 'unknown error'));
        }

        return $signingInput . '.' . Jwt::base64UrlEncode($signature);
    }
}
