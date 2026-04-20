<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Google;

use Jolicode\WalletKit\Api\Auth\GoogleOAuth2Authenticator;
use Jolicode\WalletKit\Api\Credentials\GoogleCredentials;
use Jolicode\WalletKit\Api\Google\GoogleWalletClient;
use Jolicode\WalletKit\Builder\GoogleVerticalEnum;
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Exception\Api\RateLimitException;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericClass;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class GoogleWalletClientTest extends TestCase
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

    private function createClient(MockHttpClient $httpClient, ?NormalizerInterface $normalizer = null): GoogleWalletClient
    {
        $normalizer ??= $this->createStub(NormalizerInterface::class);

        // Create a real authenticator with a mock HTTP client that returns a token
        $authHttpClient = new MockHttpClient(new MockResponse(json_encode([
            'access_token' => 'test-token',
            'expires_in' => 3600,
        ], \JSON_THROW_ON_ERROR)));

        $credentials = new GoogleCredentials($this->serviceAccountPath);
        $authenticator = new GoogleOAuth2Authenticator($authHttpClient, $credentials);

        return new GoogleWalletClient($httpClient, $normalizer, $authenticator);
    }

    private function createPair(): GoogleWalletPair
    {
        return new GoogleWalletPair(
            GoogleVerticalEnum::GENERIC,
            new GenericClass(id: 'issuer.genericClass'),
            new GenericObject(id: 'issuer.genericObject', classId: 'issuer.genericClass'),
        );
    }

    public function testCreateClassSendsPostToCorrectUrl(): void
    {
        $lastRequest = null;
        $httpClient = new MockHttpClient(function ($method, $url) use (&$lastRequest) {
            $lastRequest = ['method' => $method, 'url' => $url];

            return new MockResponse('{"id":"issuer.genericClass"}', ['http_code' => 200]);
        });

        $normalizer = $this->createStub(NormalizerInterface::class);
        $normalizer->method('normalize')->willReturn(['id' => 'issuer.genericClass']);

        $client = $this->createClient($httpClient, $normalizer);
        $response = $client->createClass($this->createPair());

        self::assertSame('POST', $lastRequest['method']);
        self::assertStringContainsString('genericClass', $lastRequest['url']);
        self::assertTrue($response->isSuccessful());
    }

    public function testGetClassSendsGetToCorrectUrl(): void
    {
        $lastRequest = null;
        $httpClient = new MockHttpClient(function ($method, $url) use (&$lastRequest) {
            $lastRequest = ['method' => $method, 'url' => $url];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $client = $this->createClient($httpClient);
        $client->getClass(GoogleVerticalEnum::LOYALTY, 'issuer.loyaltyClass');

        self::assertSame('GET', $lastRequest['method']);
        self::assertStringContainsString('loyaltyClass/issuer.loyaltyClass', $lastRequest['url']);
    }

    public function testUpdateClassSendsPut(): void
    {
        $lastRequest = null;
        $httpClient = new MockHttpClient(function ($method, $url) use (&$lastRequest) {
            $lastRequest = ['method' => $method, 'url' => $url];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $normalizer = $this->createStub(NormalizerInterface::class);
        $normalizer->method('normalize')->willReturn(['id' => 'issuer.genericClass']);

        $client = $this->createClient($httpClient, $normalizer);
        $client->updateClass($this->createPair());

        self::assertSame('PUT', $lastRequest['method']);
        self::assertStringContainsString('genericClass/issuer.genericClass', $lastRequest['url']);
    }

    public function testPatchClassSendsPatch(): void
    {
        $lastRequest = null;
        $httpClient = new MockHttpClient(function ($method, $url) use (&$lastRequest) {
            $lastRequest = ['method' => $method, 'url' => $url];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $normalizer = $this->createStub(NormalizerInterface::class);
        $normalizer->method('normalize')->willReturn(['id' => 'issuer.genericClass']);

        $client = $this->createClient($httpClient, $normalizer);
        $client->patchClass($this->createPair());

        self::assertSame('PATCH', $lastRequest['method']);
    }

    public function testCreateObjectSendsPost(): void
    {
        $lastRequest = null;
        $httpClient = new MockHttpClient(function ($method, $url) use (&$lastRequest) {
            $lastRequest = ['method' => $method, 'url' => $url];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $normalizer = $this->createStub(NormalizerInterface::class);
        $normalizer->method('normalize')->willReturn(['id' => 'issuer.genericObject']);

        $client = $this->createClient($httpClient, $normalizer);
        $client->createObject($this->createPair());

        self::assertSame('POST', $lastRequest['method']);
        self::assertStringContainsString('genericObject', $lastRequest['url']);
        self::assertStringNotContainsString('genericObject/', $lastRequest['url']);
    }

    public function testGetObjectSendsGet(): void
    {
        $lastRequest = null;
        $httpClient = new MockHttpClient(function ($method, $url) use (&$lastRequest) {
            $lastRequest = ['method' => $method, 'url' => $url];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $client = $this->createClient($httpClient);
        $client->getObject(GoogleVerticalEnum::EVENT_TICKET, 'issuer.eventObj');

        self::assertSame('GET', $lastRequest['method']);
        self::assertStringContainsString('eventTicketObject/issuer.eventObj', $lastRequest['url']);
    }

    public function testAuthorizationHeaderIsSent(): void
    {
        $capturedOptions = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$capturedOptions) {
            $capturedOptions = $options;

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $client = $this->createClient($httpClient);
        $client->getClass(GoogleVerticalEnum::GENERIC, 'test');

        self::assertNotNull($capturedOptions);
        $headers = $capturedOptions['headers'] ?? [];
        $authFound = false;
        foreach ($headers as $header) {
            if (\is_string($header) && str_starts_with(strtolower($header), 'authorization:')) {
                self::assertStringContainsString('Bearer ', $header);
                $authFound = true;
            }
        }

        self::assertTrue($authFound, 'Authorization header should be present');
    }

    public function testVerticalUrlMapping(): void
    {
        $urls = [];
        $httpClient = new MockHttpClient(function ($method, $url) use (&$urls) {
            $urls[] = $url;

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $client = $this->createClient($httpClient);

        $verticals = [
            ['vertical' => GoogleVerticalEnum::FLIGHT, 'expected' => 'flightClass'],
            ['vertical' => GoogleVerticalEnum::EVENT_TICKET, 'expected' => 'eventTicketClass'],
            ['vertical' => GoogleVerticalEnum::GIFT_CARD, 'expected' => 'giftCardClass'],
            ['vertical' => GoogleVerticalEnum::LOYALTY, 'expected' => 'loyaltyClass'],
            ['vertical' => GoogleVerticalEnum::OFFER, 'expected' => 'offerClass'],
            ['vertical' => GoogleVerticalEnum::TRANSIT, 'expected' => 'transitClass'],
            ['vertical' => GoogleVerticalEnum::GENERIC, 'expected' => 'genericClass'],
        ];

        foreach ($verticals as $entry) {
            $client->getClass($entry['vertical'], 'test-id');
        }

        foreach ($verticals as $i => $entry) {
            self::assertStringContainsString($entry['expected'], $urls[$i]);
        }
    }

    public function testRateLimitExceptionOn429(): void
    {
        $httpClient = new MockHttpClient(new MockResponse('{"error":"rate limited"}', [
            'http_code' => 429,
            'response_headers' => ['retry-after' => '30'],
        ]));

        $client = $this->createClient($httpClient);

        $this->expectException(RateLimitException::class);
        $client->getClass(GoogleVerticalEnum::GENERIC, 'test');
    }

    public function testCreateOrUpdatePassCreatesClassAndObject(): void
    {
        $requestCount = 0;
        $httpClient = new MockHttpClient(function () use (&$requestCount) {
            ++$requestCount;

            return new MockResponse('{"id":"test"}', ['http_code' => 200]);
        });

        $normalizer = $this->createStub(NormalizerInterface::class);
        $normalizer->method('normalize')->willReturn(['id' => 'test']);

        $client = $this->createClient($httpClient, $normalizer);
        $client->createOrUpdatePass($this->createPair());

        // At least 2 requests: createClass + createObject
        self::assertGreaterThanOrEqual(2, $requestCount);
    }

    public function testCreateOrUpdatePassUpdatesObjectOn409(): void
    {
        $requestMethods = [];
        $callIndex = 0;
        $httpClient = new MockHttpClient(function ($method) use (&$requestMethods, &$callIndex) {
            $requestMethods[] = $method;
            ++$callIndex;

            // 1st call: createClass -> 200
            // 2nd call: createObject -> 409
            // 3rd call: updateObject -> 200
            return match ($callIndex) {
                1 => new MockResponse('{}', ['http_code' => 200]),
                2 => new MockResponse('{"error":"conflict"}', ['http_code' => 409]),
                default => new MockResponse('{}', ['http_code' => 200]),
            };
        });

        $normalizer = $this->createStub(NormalizerInterface::class);
        $normalizer->method('normalize')->willReturn(['id' => 'test']);

        $client = $this->createClient($httpClient, $normalizer);
        $client->createOrUpdatePass($this->createPair());

        self::assertCount(3, $requestMethods);
        self::assertSame('POST', $requestMethods[0]); // createClass
        self::assertSame('POST', $requestMethods[1]); // createObject
        self::assertSame('PUT', $requestMethods[2]);   // updateObject
    }
}
