<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Credentials;

use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use PHPUnit\Framework\TestCase;

final class SamsungCredentialsTest extends TestCase
{
    public function testConstructWithRequiredOnly(): void
    {
        $credentials = new SamsungCredentials(
            partnerId: 'partner-123',
            privateKeyPath: '/path/to/key.pem',
        );

        self::assertSame('partner-123', $credentials->partnerId);
        self::assertSame('/path/to/key.pem', $credentials->privateKeyPath);
        self::assertNull($credentials->serviceId);
    }

    public function testConstructWithAllParameters(): void
    {
        $credentials = new SamsungCredentials(
            partnerId: 'partner-123',
            privateKeyPath: '/path/to/key.pem',
            serviceId: 'service-456',
        );

        self::assertSame('partner-123', $credentials->partnerId);
        self::assertSame('/path/to/key.pem', $credentials->privateKeyPath);
        self::assertSame('service-456', $credentials->serviceId);
    }
}
