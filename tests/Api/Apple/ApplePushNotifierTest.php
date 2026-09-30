<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Apple;

use Jolicode\WalletKit\Api\Apple\ApplePushNotifier;
use Jolicode\WalletKit\Api\Auth\AppleApnsJwtProvider;
use Jolicode\WalletKit\Api\Credentials\AppleCredentials;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ApplePushNotifierTest extends TestCase
{
    private string $p8KeyPath;

    protected function setUp(): void
    {
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

    private function createNotifier(MockHttpClient $httpClient, bool $sandbox = false): ApplePushNotifier
    {
        $credentials = new AppleCredentials(
            certificatePath: '/dummy/cert.p12',
            certificatePassword: 'dummy',
            apnsKeyPath: $this->p8KeyPath,
            apnsKeyId: 'KEYID123',
            apnsTeamId: 'TEAMID456',
        );
        $jwtProvider = new AppleApnsJwtProvider($credentials);

        return new ApplePushNotifier($httpClient, $jwtProvider, $sandbox);
    }

    public function testSendUpdateNotificationSuccessful(): void
    {
        $lastRequest = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$lastRequest) {
            $lastRequest = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('', [
                'http_code' => 200,
                'response_headers' => ['apns-id' => 'uuid-123'],
            ]);
        });

        $notifier = $this->createNotifier($httpClient);
        $response = $notifier->sendUpdateNotification('device-token-abc', 'pass.com.example');

        self::assertTrue($response->isSuccessful());
        self::assertSame('device-token-abc', $response->getPushToken());
        self::assertSame('uuid-123', $response->getApnsId());

        self::assertSame('POST', $lastRequest['method']);
        self::assertStringContainsString('/3/device/device-token-abc', $lastRequest['url']);
    }

    public function testSandboxUsesCorrectHost(): void
    {
        $lastUrl = null;
        $httpClient = new MockHttpClient(function ($method, $url) use (&$lastUrl) {
            $lastUrl = $url;

            return new MockResponse('', ['http_code' => 200]);
        });

        $notifier = $this->createNotifier($httpClient, sandbox: true);
        $notifier->sendUpdateNotification('token', 'pass.com.example');

        self::assertStringContainsString('api.sandbox.push.apple.com', $lastUrl);
    }

    public function testProductionUsesCorrectHost(): void
    {
        $lastUrl = null;
        $httpClient = new MockHttpClient(function ($method, $url) use (&$lastUrl) {
            $lastUrl = $url;

            return new MockResponse('', ['http_code' => 200]);
        });

        $notifier = $this->createNotifier($httpClient, sandbox: false);
        $notifier->sendUpdateNotification('token', 'pass.com.example');

        self::assertStringContainsString('api.push.apple.com', $lastUrl);
        self::assertStringNotContainsString('sandbox', $lastUrl);
    }

    public function testSendUpdateNotificationHeaders(): void
    {
        $capturedOptions = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$capturedOptions) {
            $capturedOptions = $options;

            return new MockResponse('', ['http_code' => 200]);
        });

        $notifier = $this->createNotifier($httpClient);
        $notifier->sendUpdateNotification('token', 'pass.com.example.loyalty');

        self::assertNotNull($capturedOptions);
        $headers = $capturedOptions['headers'] ?? [];
        $headerMap = [];
        foreach ($headers as $header) {
            if (\is_string($header)) {
                [$name, $value] = explode(':', $header, 2);
                $headerMap[strtolower(trim($name))] = trim($value);
            }
        }

        self::assertStringStartsWith('bearer ', $headerMap['authorization'] ?? '');
        self::assertSame('pass.com.example.loyalty', $headerMap['apns-topic'] ?? '');
        self::assertSame('background', $headerMap['apns-push-type'] ?? '');
    }

    public function testSendUpdateNotification410Gone(): void
    {
        $httpClient = new MockHttpClient(new MockResponse('{"reason":"Unregistered"}', [
            'http_code' => 410,
        ]));

        $notifier = $this->createNotifier($httpClient);
        $response = $notifier->sendUpdateNotification('expired-token', 'pass.com.example');

        self::assertTrue($response->isDeviceTokenInactive());
        self::assertSame('Unregistered', $response->getErrorReason());
    }

    public function testSendUpdateNotification429RateLimited(): void
    {
        $httpClient = new MockHttpClient(new MockResponse('{"reason":"TooManyRequests"}', [
            'http_code' => 429,
        ]));

        $notifier = $this->createNotifier($httpClient);
        $response = $notifier->sendUpdateNotification('token', 'pass.com.example');

        self::assertTrue($response->isRateLimited());
    }

    public function testSendBatchUpdateNotifications(): void
    {
        $requestCount = 0;
        $httpClient = new MockHttpClient(function () use (&$requestCount) {
            ++$requestCount;

            return new MockResponse('', [
                'http_code' => 200,
                'response_headers' => ['apns-id' => 'batch-' . $requestCount],
            ]);
        });

        $notifier = $this->createNotifier($httpClient);
        $responses = $notifier->sendBatchUpdateNotifications(
            ['token-1', 'token-2', 'token-3'],
            'pass.com.example',
        );

        self::assertCount(3, $responses);
        self::assertSame(3, $requestCount);

        foreach ($responses as $response) {
            self::assertTrue($response->isSuccessful());
        }
    }

    public function testSendBatchEmptyTokensReturnsEmpty(): void
    {
        $httpClient = new MockHttpClient([]);
        $notifier = $this->createNotifier($httpClient);

        $responses = $notifier->sendBatchUpdateNotifications([], 'pass.com.example');

        self::assertSame([], $responses);
    }

    public function testSendBatchMixedResponses(): void
    {
        $callIndex = 0;
        $httpClient = new MockHttpClient(function () use (&$callIndex) {
            ++$callIndex;

            return match ($callIndex) {
                1 => new MockResponse('', ['http_code' => 200]),
                2 => new MockResponse('{"reason":"Unregistered"}', ['http_code' => 410]),
                default => new MockResponse('', ['http_code' => 200]),
            };
        });

        $notifier = $this->createNotifier($httpClient);
        $responses = $notifier->sendBatchUpdateNotifications(
            ['token-ok', 'token-gone', 'token-ok2'],
            'pass.com.example',
        );

        self::assertCount(3, $responses);

        $inactive = array_filter($responses, fn ($r) => $r->isDeviceTokenInactive());
        self::assertCount(1, $inactive);
    }
}
