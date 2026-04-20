<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Google;

use Jolicode\WalletKit\Api\Google\GoogleApiResponse;
use PHPUnit\Framework\TestCase;

final class GoogleApiResponseTest extends TestCase
{
    public function testIsSuccessfulFor2xx(): void
    {
        self::assertTrue((new GoogleApiResponse(200, []))->isSuccessful());
        self::assertTrue((new GoogleApiResponse(201, []))->isSuccessful());
        self::assertTrue((new GoogleApiResponse(299, []))->isSuccessful());
    }

    public function testIsNotSuccessfulForNon2xx(): void
    {
        self::assertFalse((new GoogleApiResponse(400, []))->isSuccessful());
        self::assertFalse((new GoogleApiResponse(404, []))->isSuccessful());
        self::assertFalse((new GoogleApiResponse(500, []))->isSuccessful());
    }

    public function testGetStatusCode(): void
    {
        self::assertSame(201, (new GoogleApiResponse(201, []))->getStatusCode());
    }

    public function testGetData(): void
    {
        $data = ['id' => 'test-id', 'name' => 'Test'];
        self::assertSame($data, (new GoogleApiResponse(200, $data))->getData());
    }
}
