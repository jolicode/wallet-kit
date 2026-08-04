<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Samsung;

use Jolicode\WalletKit\Api\Auth\SamsungJwtAuthenticator;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Api\Samsung\SamsungWalletClient;
use Jolicode\WalletKit\Exception\Api\RateLimitException;
use Jolicode\WalletKit\Pass\Samsung\Model\Card;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardSubTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardTypeEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SamsungWalletClientTest extends TestCase
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

    private function createClient(MockHttpClient $httpClient, ?NormalizerInterface $normalizer = null): SamsungWalletClient
    {
        $normalizer ??= $this->createStub(NormalizerInterface::class);
        $credentials = new SamsungCredentials('partner-123', $this->privateKeyPath);
        $authenticator = new SamsungJwtAuthenticator($credentials);

        return new SamsungWalletClient($httpClient, $normalizer, $authenticator, $credentials);
    }

    private function createCard(): Card
    {
        return new Card(
            type: CardTypeEnum::GENERIC,
            subType: CardSubTypeEnum::OTHERS,
            data: [],
        );
    }

    public function testCreateCardSendsPost(): void
    {
        $lastRequest = null;
        $httpClient = new MockHttpClient(function ($method, $url) use (&$lastRequest) {
            $lastRequest = ['method' => $method, 'url' => $url];

            return new MockResponse('{"cardId":"card-123"}', ['http_code' => 201]);
        });

        $normalizer = $this->createStub(NormalizerInterface::class);
        $normalizer->method('normalize')->willReturn(['card' => ['type' => 'generic']]);

        $client = $this->createClient($httpClient, $normalizer);
        $response = $client->createCard($this->createCard());

        self::assertSame('POST', $lastRequest['method']);
        self::assertStringContainsString('/cards', $lastRequest['url']);
        self::assertTrue($response->isSuccessful());
    }

    public function testGetCardSendsGet(): void
    {
        $lastRequest = null;
        $httpClient = new MockHttpClient(function ($method, $url) use (&$lastRequest) {
            $lastRequest = ['method' => $method, 'url' => $url];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $client = $this->createClient($httpClient);
        $client->getCard('card-123');

        self::assertSame('GET', $lastRequest['method']);
        self::assertStringContainsString('/cards/card-123', $lastRequest['url']);
    }

    public function testUpdateCardSendsPut(): void
    {
        $lastRequest = null;
        $httpClient = new MockHttpClient(function ($method, $url) use (&$lastRequest) {
            $lastRequest = ['method' => $method, 'url' => $url];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $normalizer = $this->createStub(NormalizerInterface::class);
        $normalizer->method('normalize')->willReturn(['card' => []]);

        $client = $this->createClient($httpClient, $normalizer);
        $client->updateCard($this->createCard(), 'card-123');

        self::assertSame('PUT', $lastRequest['method']);
        self::assertStringContainsString('/cards/card-123', $lastRequest['url']);
    }

    public function testUpdateCardStateSendsPatch(): void
    {
        $lastRequest = null;
        $httpClient = new MockHttpClient(function ($method, $url) use (&$lastRequest) {
            $lastRequest = ['method' => $method, 'url' => $url];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $client = $this->createClient($httpClient);
        $client->updateCardState('card-123', 'expired');

        self::assertSame('PATCH', $lastRequest['method']);
        self::assertStringContainsString('/cards/card-123', $lastRequest['url']);
    }

    public function testPushCardUpdateSendsPost(): void
    {
        $lastRequest = null;
        $httpClient = new MockHttpClient(function ($method, $url) use (&$lastRequest) {
            $lastRequest = ['method' => $method, 'url' => $url];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $client = $this->createClient($httpClient);
        $client->pushCardUpdate('card-123');

        self::assertSame('POST', $lastRequest['method']);
        self::assertStringContainsString('/cards/card-123/push', $lastRequest['url']);
    }

    public function testRateLimitExceptionOn429(): void
    {
        $httpClient = new MockHttpClient(new MockResponse('{"error":"rate limited"}', [
            'http_code' => 429,
            'response_headers' => ['retry-after' => '60'],
        ]));

        $client = $this->createClient($httpClient);

        $this->expectException(RateLimitException::class);
        $client->getCard('card-123');
    }
}
