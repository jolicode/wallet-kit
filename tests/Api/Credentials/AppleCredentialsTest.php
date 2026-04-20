<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Credentials;

use Jolicode\WalletKit\Api\Credentials\AppleCredentials;
use PHPUnit\Framework\TestCase;

final class AppleCredentialsTest extends TestCase
{
    public function testConstructWithRequiredOnly(): void
    {
        $credentials = new AppleCredentials(
            certificatePath: '/path/to/cert.p12',
            certificatePassword: 'secret',
        );

        self::assertSame('/path/to/cert.p12', $credentials->certificatePath);
        self::assertSame('secret', $credentials->certificatePassword);
        self::assertNull($credentials->wwdrCertificatePath);
        self::assertNull($credentials->apnsKeyPath);
        self::assertNull($credentials->apnsKeyId);
        self::assertNull($credentials->apnsTeamId);
        self::assertNull($credentials->teamIdentifier);
        self::assertNull($credentials->passTypeIdentifier);
    }

    public function testConstructWithAllParameters(): void
    {
        $credentials = new AppleCredentials(
            certificatePath: '/path/to/cert.p12',
            certificatePassword: 'secret',
            wwdrCertificatePath: '/path/to/wwdr.cer',
            apnsKeyPath: '/path/to/apns.p8',
            apnsKeyId: 'KEY123',
            apnsTeamId: 'TEAM456',
            teamIdentifier: 'TEAM456',
            passTypeIdentifier: 'pass.com.example',
        );

        self::assertSame('/path/to/cert.p12', $credentials->certificatePath);
        self::assertSame('secret', $credentials->certificatePassword);
        self::assertSame('/path/to/wwdr.cer', $credentials->wwdrCertificatePath);
        self::assertSame('/path/to/apns.p8', $credentials->apnsKeyPath);
        self::assertSame('KEY123', $credentials->apnsKeyId);
        self::assertSame('TEAM456', $credentials->apnsTeamId);
        self::assertSame('TEAM456', $credentials->teamIdentifier);
        self::assertSame('pass.com.example', $credentials->passTypeIdentifier);
    }
}
