<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Auth;

use Jolicode\WalletKit\Api\Auth\CachedToken;
use Jolicode\WalletKit\Api\Auth\TokenInterface;
use PHPUnit\Framework\TestCase;

final class CachedTokenTest extends TestCase
{
    public function testImplementsTokenInterface(): void
    {
        $token = new CachedToken('access-token', new \DateTimeImmutable('+1 hour'));

        self::assertInstanceOf(TokenInterface::class, $token);
    }

    public function testGetAccessToken(): void
    {
        $token = new CachedToken('my-access-token', new \DateTimeImmutable('+1 hour'));

        self::assertSame('my-access-token', $token->getAccessToken());
    }

    public function testIsNotExpiredWhenInFuture(): void
    {
        $token = new CachedToken('token', new \DateTimeImmutable('+1 hour'));

        self::assertFalse($token->isExpired());
    }

    public function testIsExpiredWhenInPast(): void
    {
        $token = new CachedToken('token', new \DateTimeImmutable('-1 second'));

        self::assertTrue($token->isExpired());
    }
}
