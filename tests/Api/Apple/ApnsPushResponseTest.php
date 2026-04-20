<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Apple;

use Jolicode\WalletKit\Api\Apple\ApnsPushResponse;
use PHPUnit\Framework\TestCase;

final class ApnsPushResponseTest extends TestCase
{
    public function testIsSuccessful(): void
    {
        $response = new ApnsPushResponse('token-123', 200, null, 'apns-id-456');

        self::assertTrue($response->isSuccessful());
        self::assertFalse($response->isDeviceTokenInactive());
        self::assertFalse($response->isRateLimited());
    }

    public function testIsDeviceTokenInactive(): void
    {
        $response = new ApnsPushResponse('token-123', 410, 'Unregistered');

        self::assertFalse($response->isSuccessful());
        self::assertTrue($response->isDeviceTokenInactive());
        self::assertFalse($response->isRateLimited());
    }

    public function testIsRateLimited(): void
    {
        $response = new ApnsPushResponse('token-123', 429, 'TooManyRequests');

        self::assertFalse($response->isSuccessful());
        self::assertFalse($response->isDeviceTokenInactive());
        self::assertTrue($response->isRateLimited());
    }

    public function testGetters(): void
    {
        $response = new ApnsPushResponse('token-abc', 200, null, 'apns-uuid');

        self::assertSame('token-abc', $response->getPushToken());
        self::assertSame(200, $response->getStatusCode());
        self::assertNull($response->getErrorReason());
        self::assertSame('apns-uuid', $response->getApnsId());
    }

    public function testErrorResponse(): void
    {
        $response = new ApnsPushResponse('token-xyz', 400, 'BadDeviceToken');

        self::assertSame('BadDeviceToken', $response->getErrorReason());
        self::assertNull($response->getApnsId());
    }
}
