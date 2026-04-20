<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Auth;

use Jolicode\WalletKit\Api\Auth\SamsungJwtAuthenticator;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Exception\Api\AuthenticationException;
use PHPUnit\Framework\TestCase;

final class SamsungJwtAuthenticatorTest extends TestCase
{
    private string $privateKeyPath;

    protected function setUp(): void
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => \OPENSSL_KEYTYPE_RSA]);
        self::assertNotFalse($key);
        openssl_pkey_export($key, $privatePem);

        $this->privateKeyPath = tempnam(sys_get_temp_dir(), 'wallet_kit_samsung_key_') ?: '';
        file_put_contents($this->privateKeyPath, $privatePem);
    }

    protected function tearDown(): void
    {
        @unlink($this->privateKeyPath);
    }

    public function testGetTokenReturnsJwt(): void
    {
        $credentials = new SamsungCredentials('partner-123', $this->privateKeyPath);
        $authenticator = new SamsungJwtAuthenticator($credentials);

        $token = $authenticator->getToken();

        self::assertFalse($token->isExpired());

        // Verify it's a valid JWT format (3 dot-separated parts)
        $parts = explode('.', $token->getAccessToken());
        self::assertCount(3, $parts);

        // Verify header
        $header = json_decode(base64_decode(strtr($parts[0], '-_', '+/'), true), true);
        self::assertSame('RS256', $header['alg']);
        self::assertSame('JWT', $header['typ']);

        // Verify claims
        $claims = json_decode(base64_decode(strtr($parts[1], '-_', '+/'), true), true);
        self::assertSame('partner-123', $claims['iss']);
        self::assertArrayHasKey('iat', $claims);
        self::assertArrayHasKey('exp', $claims);
    }

    public function testGetTokenCachesToken(): void
    {
        $credentials = new SamsungCredentials('partner-123', $this->privateKeyPath);
        $authenticator = new SamsungJwtAuthenticator($credentials);

        $token1 = $authenticator->getToken();
        $token2 = $authenticator->getToken();

        self::assertSame($token1->getAccessToken(), $token2->getAccessToken());
    }

    public function testThrowsOnUnreadableKeyFile(): void
    {
        $credentials = new SamsungCredentials('partner-123', '/nonexistent/key.pem');
        $authenticator = new SamsungJwtAuthenticator($credentials);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Unable to read Samsung private key');
        $authenticator->getToken();
    }

    public function testThrowsOnInvalidKeyContent(): void
    {
        $badKeyPath = tempnam(sys_get_temp_dir(), 'wallet_kit_bad_key_') ?: '';
        file_put_contents($badKeyPath, 'not-a-valid-key');

        try {
            $credentials = new SamsungCredentials('partner-123', $badKeyPath);
            $authenticator = new SamsungJwtAuthenticator($credentials);

            $this->expectException(AuthenticationException::class);
            $this->expectExceptionMessage('Unable to parse Samsung private key');
            $authenticator->getToken();
        } finally {
            @unlink($badKeyPath);
        }
    }
}
