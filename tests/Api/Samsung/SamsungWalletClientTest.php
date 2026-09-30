<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Samsung;

use Jolicode\WalletKit\Api\Auth\SamsungRequestAuthenticator;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Api\Samsung\SamsungRegionEnum;
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
        $credentials = new SamsungCredentials(
            'partner-123',
            $this->privateKeyPath,
            'cert-0001',
            region: SamsungRegionEnum::EU,
        );
        $authenticator = new SamsungRequestAuthenticator($credentials);

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

    /**
     * MockHttpClient normalizes headers to "Name: value" strings.
     *
     * @param list<string> $headers
     *
     * @return array<string, string>
     */
    private static function headerMap(array $headers): array
    {
        $map = [];
        foreach ($headers as $line) {
            [$name, $value] = explode(':', $line, 2);
            $map[trim($name)] = trim($value);
        }

        return $map;
    }

    public function testCreateCardSendsTokenizedTemplateRequest(): void
    {
        $capture = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$capture) {
            $capture = ['method' => $method, 'url' => $url, 'headers' => $options['headers'] ?? [], 'body' => $options['body'] ?? null];

            return new MockResponse('{"code":"0","msg":"SUCCESS"}', ['http_code' => 200]);
        });

        $normalizer = $this->createStub(NormalizerInterface::class);
        $normalizer->method('normalize')->willReturn(['card' => ['type' => 'generic']]);

        $client = $this->createClient($httpClient, $normalizer);
        $response = $client->createCard($this->createCard());

        self::assertNotNull($capture);
        self::assertSame('POST', $capture['method']);
        self::assertSame('https://api-eu1.mpay.samsung.com/partner/v1/card/template', $capture['url']);

        $headers = self::headerMap($capture['headers']);
        self::assertStringStartsWith('Bearer ey', $headers['Authorization'] ?? '');
        self::assertSame('partner-123', $headers['x-smcs-partner-id'] ?? '');
        self::assertArrayHasKey('x-request-id', $headers);
        self::assertTrue($response->isSuccessful());
    }

    public function testUpdateCardPostsToPathWithCardId(): void
    {
        $capture = null;
        $httpClient = new MockHttpClient(function ($method, $url) use (&$capture) {
            $capture = ['method' => $method, 'url' => $url];

            return new MockResponse('{"code":"0"}', ['http_code' => 200]);
        });

        $normalizer = $this->createStub(NormalizerInterface::class);
        $normalizer->method('normalize')->willReturn(['card' => []]);

        $client = $this->createClient($httpClient, $normalizer);
        $client->updateCard($this->createCard(), 'card-123');

        self::assertNotNull($capture);
        self::assertSame('POST', $capture['method']);
        self::assertSame('https://api-eu1.mpay.samsung.com/partner/v1/card/template/card-123', $capture['url']);
    }

    public function testPushCardUpdateUsesWltexEndpointWithCc2(): void
    {
        $capture = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$capture) {
            $capture = ['method' => $method, 'url' => $url, 'headers' => $options['headers'] ?? []];

            return new MockResponse('{"code":"0"}', ['http_code' => 200]);
        });

        $client = $this->createClient($httpClient);
        $client->pushCardUpdate('card-123', 'event-1', 'STATE_CHANGED');

        self::assertNotNull($capture);
        self::assertSame('POST', $capture['method']);
        self::assertSame('https://api-eu1.mpay.samsung.com/wltex/cards/card-123?eventId=event-1&type=STATE_CHANGED', $capture['url']);

        $headers = self::headerMap($capture['headers']);
        self::assertSame('EU', $headers['x-smcs-cc2'] ?? '');
    }

    public function testRateLimitExceptionOn429CarriesRetryAfter(): void
    {
        $httpClient = new MockHttpClient(new MockResponse('{"error":"rate limited"}', [
            'http_code' => 429,
            'response_headers' => ['Retry-After' => '60'],
        ]));

        $client = $this->createClient($httpClient);

        try {
            $client->createCard($this->createCard());
            self::fail('RateLimitException expected.');
        } catch (RateLimitException $e) {
            self::assertSame(60, $e->retryAfterSeconds);
        }
    }

    public function test429WithHttpDateRetryAfterIsIgnored(): void
    {
        $httpClient = new MockHttpClient(new MockResponse('{"error":"rate limited"}', [
            'http_code' => 429,
            'response_headers' => ['Retry-After' => 'Wed, 21 Oct 2026 07:28:00 GMT'],
        ]));

        $client = $this->createClient($httpClient);

        try {
            $client->createCard($this->createCard());
            self::fail('RateLimitException expected.');
        } catch (RateLimitException $e) {
            self::assertNull($e->retryAfterSeconds);
        }
    }

    public function testNonJsonErrorBodyThrowsTypedException(): void
    {
        $httpClient = new MockHttpClient(new MockResponse('<html>Bad Gateway</html>', [
            'http_code' => 502,
        ]));

        $client = $this->createClient($httpClient);

        $this->expectException(\Jolicode\WalletKit\Exception\Api\HttpRequestException::class);
        $this->expectExceptionMessage('non-JSON');
        $client->createCard($this->createCard());
    }
}
