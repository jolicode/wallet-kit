<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Google;

use Jolicode\WalletKit\Api\Credentials\GoogleCredentials;
use Jolicode\WalletKit\Api\Google\GoogleSaveLinkGenerator;
use Jolicode\WalletKit\Builder\GoogleVerticalEnum;
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericClass;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class GoogleSaveLinkGeneratorTest extends TestCase
{
    private string $serviceAccountPath;

    protected function setUp(): void
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => \OPENSSL_KEYTYPE_RSA]);
        self::assertNotFalse($key);
        openssl_pkey_export($key, $privatePem);

        $this->serviceAccountPath = tempnam(sys_get_temp_dir(), 'wallet_kit_sa_') ?: '';
        file_put_contents($this->serviceAccountPath, json_encode([
            'type' => 'service_account',
            'client_email' => 'test@test-project.iam.gserviceaccount.com',
            'private_key' => $privatePem,
        ], \JSON_THROW_ON_ERROR));
    }

    protected function tearDown(): void
    {
        @unlink($this->serviceAccountPath);
    }

    public function testGenerateSaveLinkReturnsValidUrl(): void
    {
        $normalizer = $this->createStub(NormalizerInterface::class);
        $normalizer->method('normalize')->willReturn(['id' => 'issuer.genericObject', 'classId' => 'issuer.genericClass']);

        $credentials = new GoogleCredentials($this->serviceAccountPath);
        $generator = new GoogleSaveLinkGenerator($normalizer, $credentials);

        $pair = new GoogleWalletPair(
            GoogleVerticalEnum::GENERIC,
            new GenericClass(id: 'issuer.genericClass'),
            new GenericObject(id: 'issuer.genericObject', classId: 'issuer.genericClass'),
        );

        $link = $generator->generateSaveLink($pair);

        self::assertStringStartsWith('https://pay.google.com/gp/v/save/', $link);
    }

    public function testGenerateSaveLinkContainsValidJwt(): void
    {
        $normalizer = $this->createStub(NormalizerInterface::class);
        $normalizer->method('normalize')->willReturn(['id' => 'issuer.genericObject']);

        $credentials = new GoogleCredentials($this->serviceAccountPath);
        $generator = new GoogleSaveLinkGenerator($normalizer, $credentials);

        $pair = new GoogleWalletPair(
            GoogleVerticalEnum::GENERIC,
            new GenericClass(id: 'issuer.genericClass'),
            new GenericObject(id: 'issuer.genericObject', classId: 'issuer.genericClass'),
        );

        $link = $generator->generateSaveLink($pair);

        // Extract JWT from URL
        $jwt = substr($link, \strlen('https://pay.google.com/gp/v/save/'));
        $parts = explode('.', $jwt);
        self::assertCount(3, $parts);

        // Verify header
        $header = json_decode(base64_decode(strtr($parts[0], '-_', '+/'), true), true);
        self::assertSame('RS256', $header['alg']);

        // Verify claims
        $claims = json_decode(base64_decode(strtr($parts[1], '-_', '+/'), true), true);
        self::assertSame('test@test-project.iam.gserviceaccount.com', $claims['iss']);
        self::assertSame('google', $claims['aud']);
        self::assertSame('savetowallet', $claims['typ']);
        self::assertArrayHasKey('genericObjects', $claims['payload']);
        self::assertCount(1, $claims['payload']['genericObjects']);
    }

    public function testPayloadKeyMatchesVertical(): void
    {
        $normalizer = $this->createStub(NormalizerInterface::class);
        $normalizer->method('normalize')->willReturn(['id' => 'test']);

        $credentials = new GoogleCredentials($this->serviceAccountPath);
        $generator = new GoogleSaveLinkGenerator($normalizer, $credentials);

        $pair = new GoogleWalletPair(
            GoogleVerticalEnum::LOYALTY,
            new \Jolicode\WalletKit\Pass\Android\Model\Loyalty\LoyaltyClass(
                id: 'issuer.loyaltyClass',
                issuerName: 'Test Issuer',
                reviewStatus: \Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum::DRAFT,
            ),
            new \Jolicode\WalletKit\Pass\Android\Model\Loyalty\LoyaltyObject(
                id: 'issuer.loyaltyObject',
                classId: 'issuer.loyaltyClass',
                state: \Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum::ACTIVE,
            ),
        );

        $link = $generator->generateSaveLink($pair);
        $jwt = substr($link, \strlen('https://pay.google.com/gp/v/save/'));
        $parts = explode('.', $jwt);

        $claims = json_decode(base64_decode(strtr($parts[1], '-_', '+/'), true), true);
        self::assertArrayHasKey('loyaltyObjects', $claims['payload']);
    }
}
