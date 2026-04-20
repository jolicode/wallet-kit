<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Auth;

use Jolicode\WalletKit\Api\Credentials\AppleCredentials;
use Jolicode\WalletKit\Exception\Api\AuthenticationException;
use Jolicode\WalletKit\Exception\Api\MissingExtensionException;

final class AppleApnsJwtProvider
{
    /** Cache for ~50 minutes (Apple tokens expire after 1 hour) */
    private const TOKEN_TTL_SECONDS = 3000;

    private ?CachedToken $cachedToken = null;

    public function __construct(
        private readonly AppleCredentials $credentials,
    ) {
        if (!\extension_loaded('openssl')) {
            throw new MissingExtensionException('The "openssl" PHP extension is required for Apple APNS JWT authentication.');
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
        $keyPath = $this->credentials->apnsKeyPath;
        $keyId = $this->credentials->apnsKeyId;
        $teamId = $this->credentials->apnsTeamId;

        if (null === $keyPath || null === $keyId || null === $teamId) {
            throw new AuthenticationException('Apple APNS credentials require apnsKeyPath, apnsKeyId, and apnsTeamId to be set.');
        }

        $now = time();

        $header = self::base64UrlEncode(json_encode([
            'alg' => 'ES256',
            'kid' => $keyId,
        ], \JSON_THROW_ON_ERROR));

        $claims = self::base64UrlEncode(json_encode([
            'iss' => $teamId,
            'iat' => $now,
        ], \JSON_THROW_ON_ERROR));

        $signingInput = $header . '.' . $claims;

        $keyContent = @file_get_contents($keyPath);

        if (false === $keyContent) {
            throw new AuthenticationException(\sprintf('Unable to read Apple APNS P8 key at "%s".', $keyPath));
        }

        $key = openssl_pkey_get_private($keyContent);

        if (false === $key) {
            throw new AuthenticationException('Unable to parse Apple APNS P8 key.');
        }

        $signature = '';

        if (!openssl_sign($signingInput, $signature, $key, \OPENSSL_ALGO_SHA256)) {
            throw new AuthenticationException(\sprintf('Failed to sign APNS JWT: %s', openssl_error_string() ?: 'unknown error'));
        }

        // Convert DER signature to raw R+S format for ES256
        $signature = self::derToRs($signature);

        $jwt = $signingInput . '.' . self::base64UrlEncode($signature);

        return new CachedToken(
            $jwt,
            new \DateTimeImmutable(\sprintf('+%d seconds', self::TOKEN_TTL_SECONDS)),
        );
    }

    /**
     * Convert a DER-encoded ECDSA signature to the raw R+S concatenation (64 bytes for ES256).
     */
    private static function derToRs(string $der): string
    {
        $offset = 2;

        // Read R
        if ("\x02" !== $der[$offset]) {
            throw new AuthenticationException('Invalid DER signature: expected integer tag for R.');
        }
        ++$offset;
        $rLength = \ord($der[$offset]);
        ++$offset;
        $r = substr($der, $offset, $rLength);
        $offset += $rLength;

        // Read S
        if ("\x02" !== $der[$offset]) {
            throw new AuthenticationException('Invalid DER signature: expected integer tag for S.');
        }
        ++$offset;
        $sLength = \ord($der[$offset]);
        ++$offset;
        $s = substr($der, $offset, $sLength);

        // Pad or trim to 32 bytes each
        $r = str_pad(ltrim($r, "\x00"), 32, "\x00", \STR_PAD_LEFT);
        $s = str_pad(ltrim($s, "\x00"), 32, "\x00", \STR_PAD_LEFT);

        return $r . $s;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
