<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api;

use Jolicode\WalletKit\Api\Credentials\GoogleCredentials;
use Jolicode\WalletKit\Api\Google\GoogleSaveLinkGenerator;
use Jolicode\WalletKit\Api\IssuanceHelper;
use Jolicode\WalletKit\Builder\GoogleVerticalEnum;
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericClass;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class IssuanceHelperTest extends TestCase
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
            'client_email' => 'test@test.iam.gserviceaccount.com',
            'private_key' => $privatePem,
        ], \JSON_THROW_ON_ERROR));
    }

    protected function tearDown(): void
    {
        @unlink($this->serviceAccountPath);
    }

    public function testAppleAddToWalletUrlReturnsPassthrough(): void
    {
        $helper = new IssuanceHelper();

        self::assertSame(
            'https://example.com/pass/download/123',
            $helper->appleAddToWalletUrl('https://example.com/pass/download/123'),
        );
    }

    public function testGoogleAddToWalletUrlDelegatesToGenerator(): void
    {
        $pair = new GoogleWalletPair(
            GoogleVerticalEnum::GENERIC,
            new GenericClass(id: 'issuer.genericClass'),
            new GenericObject(id: 'issuer.genericObject', classId: 'issuer.genericClass'),
        );

        $normalizer = $this->createStub(NormalizerInterface::class);
        $normalizer->method('normalize')->willReturn(['id' => 'issuer.genericObject']);

        $credentials = new GoogleCredentials($this->serviceAccountPath);
        $generator = new GoogleSaveLinkGenerator($normalizer, $credentials);

        $helper = new IssuanceHelper($generator);

        $url = $helper->googleAddToWalletUrl($pair);
        self::assertStringStartsWith('https://pay.google.com/gp/v/save/', $url);
    }

    public function testGoogleAddToWalletUrlThrowsWithoutGenerator(): void
    {
        $helper = new IssuanceHelper();

        $pair = new GoogleWalletPair(
            GoogleVerticalEnum::GENERIC,
            new GenericClass(id: 'test'),
            new GenericObject(id: 'test', classId: 'test'),
        );

        $this->expectException(\LogicException::class);
        $helper->googleAddToWalletUrl($pair);
    }

    public function testSamsungAddToWalletUrl(): void
    {
        $helper = new IssuanceHelper();

        $url = $helper->samsungAddToWalletUrl('card-123', 'partner-456');

        self::assertStringStartsWith('https://a.]wallet.samsung.com/wallet/card', $url);
        self::assertStringContainsString('cardId=card-123', $url);
        self::assertStringContainsString('partnerId=partner-456', $url);
    }
}
