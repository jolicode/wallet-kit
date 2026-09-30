<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Auth;

use Jolicode\WalletKit\Api\Auth\SamsungRequestAuthenticator;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Api\Samsung\SamsungRegionEnum;
use Jolicode\WalletKit\Exception\Api\AuthenticationException;
use PHPUnit\Framework\TestCase;

final class SamsungRequestAuthenticatorTest extends TestCase
{
    private string $privateKeyPath;

    protected function setUp(): void
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => \OPENSSL_KEYTYPE_RSA]);
        self::assertNotFalse($key);
        openssl_pkey_export($key, $privatePem);

        $this->privateKeyPath = tempnam(sys_get_temp_dir(), 'wallet_kit_sams_auth_') ?: '';
        file_put_contents($this->privateKeyPath, $privatePem);
    }

    protected function tearDown(): void
    {
        @unlink($this->privateKeyPath);
    }

    private function createAuthenticator(): SamsungRequestAuthenticator
    {
        return new SamsungRequestAuthenticator(new SamsungCredentials(
            'partner-123',
            $this->privateKeyPath,
            'YMtt',
            region: SamsungRegionEnum::KR,
        ));
    }

    /**
     * @return array{header: array<string, mixed>, payload: array<string, mixed>, signature: string, signingInput: string}
     */
    private function decode(string $jws): array
    {
        $parts = explode('.', $jws);
        self::assertCount(3, $parts);

        $header = json_decode(self::b64UrlDecode($parts[0]), true, 512, \JSON_THROW_ON_ERROR);
        $payload = json_decode(self::b64UrlDecode($parts[1]), true, 512, \JSON_THROW_ON_ERROR);
        $signature = self::b64UrlDecode($parts[2]);

        return ['header' => $header, 'payload' => $payload, 'signature' => $signature, 'signingInput' => $parts[0] . '.' . $parts[1]];
    }

    private static function b64UrlDecode(string $data): string
    {
        $decoded = base64_decode(strtr($data, '-_', '+/'), true);

        if (false === $decoded) {
            self::fail('Invalid base64url segment: ' . $data);
        }

        return $decoded;
    }

    public function testAuthorizationTokenCarriesSpecFields(): void
    {
        $authenticator = $this->createAuthenticator();
        $jws = $authenticator->createAuthorizationToken('POST', '/partner/v1/card/template/card-1');

        $decoded = $this->decode($jws);

        self::assertSame('RS256', $decoded['header']['alg']);
        self::assertSame('AUTH', $decoded['header']['cty']);
        self::assertSame('3', $decoded['header']['ver']);
        self::assertSame('YMtt', $decoded['header']['certificateId']);
        self::assertSame('partner-123', $decoded['header']['partnerId']);
        self::assertIsInt($decoded['header']['utc']);

        self::assertSame([
            'API' => [
                'method' => 'POST',
                'path' => '/partner/v1/card/template/card-1',
            ],
        ], $decoded['payload']);
    }

    public function testTokenBindsMethodAndPathPerRequest(): void
    {
        $authenticator = $this->createAuthenticator();

        $post = $this->decode($authenticator->createAuthorizationToken('POST', '/partner/v1/card/template'));
        $get = $this->decode($authenticator->createAuthorizationToken('GET', '/partner/v1/card/template/card-1'));

        self::assertSame('POST', $post['payload']['API']['method']);
        self::assertSame('GET', $get['payload']['API']['method']);
        self::assertSame('/partner/v1/card/template/card-1', $get['payload']['API']['path']);
        self::assertNotSame($post['signingInput'], $get['signingInput']);
    }

    public function testSignatureVerifiesAgainstGeneratedPublicKey(): void
    {
        $authenticator = $this->createAuthenticator();
        $jws = $authenticator->createAuthorizationToken('POST', '/atw/v1/cards/card-1');

        $decoded = $this->decode($jws);
        $privateKey = openssl_pkey_get_private(file_get_contents($this->privateKeyPath));
        $publicKey = openssl_pkey_get_details($privateKey)['key'];

        $verified = openssl_verify($decoded['signingInput'], $decoded['signature'], $publicKey, \OPENSSL_ALGO_SHA256);
        self::assertSame(1, $verified);
    }

    public function testRejectsPathWithoutLeadingSlash(): void
    {
        $authenticator = $this->createAuthenticator();

        $this->expectException(\ValueError::class);
        $authenticator->createAuthorizationToken('POST', 'partner/v1/card/template');
    }

    public function testMissingKeyFileThrowsAuthenticationException(): void
    {
        $this->expectException(AuthenticationException::class);

        new SamsungRequestAuthenticator(new SamsungCredentials(
            'partner-123',
            '/definitely/does/not/exist.pem',
            'YMtt',
            region: SamsungRegionEnum::KR,
        ));
    }
}
