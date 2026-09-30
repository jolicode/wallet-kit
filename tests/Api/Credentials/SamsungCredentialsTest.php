<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Credentials;

use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Api\Samsung\SamsungRegionEnum;
use PHPUnit\Framework\TestCase;

final class SamsungCredentialsTest extends TestCase
{
    public function testConstructWithRequiredOnly(): void
    {
        $credentials = new SamsungCredentials(
            partnerId: 'partner-123',
            privateKeyPath: '/path/to/key.pem',
            certificateId: 'YMtt',
        );

        self::assertSame('partner-123', $credentials->partnerId);
        self::assertSame('/path/to/key.pem', $credentials->privateKeyPath);
        self::assertSame('YMtt', $credentials->certificateId);
        self::assertNull($credentials->publicKeyPath);
        self::assertSame(SamsungRegionEnum::EU, $credentials->region);
    }

    public function testConstructWithAllParameters(): void
    {
        $credentials = new SamsungCredentials(
            partnerId: 'partner-123',
            privateKeyPath: '/path/to/key.pem',
            certificateId: 'YMtt',
            publicKeyPath: '/path/to/samsung.crt',
            region: SamsungRegionEnum::US,
        );

        self::assertSame('partner-123', $credentials->partnerId);
        self::assertSame('/path/to/key.pem', $credentials->privateKeyPath);
        self::assertSame('YMtt', $credentials->certificateId);
        self::assertSame('/path/to/samsung.crt', $credentials->publicKeyPath);
        self::assertSame(SamsungRegionEnum::US, $credentials->region);
    }
}
