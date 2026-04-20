<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Auth;

use Jolicode\WalletKit\Api\Auth\AppleApnsJwtProvider;
use Jolicode\WalletKit\Api\Credentials\AppleCredentials;
use Jolicode\WalletKit\Exception\Api\AuthenticationException;
use PHPUnit\Framework\TestCase;

final class AppleApnsJwtProviderTest extends TestCase
{
    private string $p8KeyPath;

    protected function setUp(): void
    {
        // Generate an EC P-256 key (used by Apple APNS)
        $key = openssl_pkey_new([
            'curve_name' => 'prime256v1',
            'private_key_type' => \OPENSSL_KEYTYPE_EC,
        ]);
        self::assertNotFalse($key);
        openssl_pkey_export($key, $privatePem);

        $this->p8KeyPath = tempnam(sys_get_temp_dir(), 'wallet_kit_apns_p8_') ?: '';
        file_put_contents($this->p8KeyPath, $privatePem);
    }

    protected function tearDown(): void
    {
        @unlink($this->p8KeyPath);
    }

    public function testGetTokenReturnsEs256Jwt(): void
    {
        $credentials = new AppleCredentials(
            certificatePath: '/dummy/cert.p12',
            certificatePassword: 'dummy',
            apnsKeyPath: $this->p8KeyPath,
            apnsKeyId: 'KEYID123',
            apnsTeamId: 'TEAMID456',
        );

        $provider = new AppleApnsJwtProvider($credentials);
        $token = $provider->getToken();

        self::assertFalse($token->isExpired());

        // Verify JWT format
        $parts = explode('.', $token->getAccessToken());
        self::assertCount(3, $parts);

        // Verify header has ES256 and kid
        $header = json_decode(base64_decode(strtr($parts[0], '-_', '+/'), true), true);
        self::assertSame('ES256', $header['alg']);
        self::assertSame('KEYID123', $header['kid']);

        // Verify claims
        $claims = json_decode(base64_decode(strtr($parts[1], '-_', '+/'), true), true);
        self::assertSame('TEAMID456', $claims['iss']);
        self::assertArrayHasKey('iat', $claims);

        // Verify signature is 64 bytes (R + S, each 32 bytes)
        $signatureBytes = base64_decode(strtr($parts[2], '-_', '+/'), true);
        self::assertNotFalse($signatureBytes);
        self::assertSame(64, \strlen($signatureBytes));
    }

    public function testGetTokenCachesToken(): void
    {
        $credentials = new AppleCredentials(
            certificatePath: '/dummy/cert.p12',
            certificatePassword: 'dummy',
            apnsKeyPath: $this->p8KeyPath,
            apnsKeyId: 'KEYID123',
            apnsTeamId: 'TEAMID456',
        );

        $provider = new AppleApnsJwtProvider($credentials);
        $token1 = $provider->getToken();
        $token2 = $provider->getToken();

        self::assertSame($token1->getAccessToken(), $token2->getAccessToken());
    }

    public function testThrowsWhenApnsCredentialsMissing(): void
    {
        $credentials = new AppleCredentials(
            certificatePath: '/dummy/cert.p12',
            certificatePassword: 'dummy',
        );

        $provider = new AppleApnsJwtProvider($credentials);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('apnsKeyPath');
        $provider->getToken();
    }

    public function testThrowsOnUnreadableKeyFile(): void
    {
        $credentials = new AppleCredentials(
            certificatePath: '/dummy/cert.p12',
            certificatePassword: 'dummy',
            apnsKeyPath: '/nonexistent/key.p8',
            apnsKeyId: 'KEYID123',
            apnsTeamId: 'TEAMID456',
        );

        $provider = new AppleApnsJwtProvider($credentials);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Unable to read Apple APNS P8 key');
        $provider->getToken();
    }

    public function testThrowsOnInvalidKeyContent(): void
    {
        $badKeyPath = tempnam(sys_get_temp_dir(), 'wallet_kit_bad_p8_') ?: '';
        file_put_contents($badKeyPath, 'not-a-valid-key');

        try {
            $credentials = new AppleCredentials(
                certificatePath: '/dummy/cert.p12',
                certificatePassword: 'dummy',
                apnsKeyPath: $badKeyPath,
                apnsKeyId: 'KEYID123',
                apnsTeamId: 'TEAMID456',
            );

            $provider = new AppleApnsJwtProvider($credentials);

            $this->expectException(AuthenticationException::class);
            $this->expectExceptionMessage('Unable to parse Apple APNS P8 key');
            $provider->getToken();
        } finally {
            @unlink($badKeyPath);
        }
    }
}
