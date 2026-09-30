<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Samsung;

use Jolicode\WalletKit\Api\Samsung\SamsungApiResponse;
use PHPUnit\Framework\TestCase;

final class SamsungApiResponseTest extends TestCase
{
    public function testIsSuccessfulFor2xx(): void
    {
        self::assertTrue((new SamsungApiResponse(200, []))->isSuccessful());
        self::assertTrue((new SamsungApiResponse(201, []))->isSuccessful());
    }

    public function testIsNotSuccessfulForNon2xx(): void
    {
        self::assertFalse((new SamsungApiResponse(400, []))->isSuccessful());
        self::assertFalse((new SamsungApiResponse(500, []))->isSuccessful());
    }

    public function testGetStatusCode(): void
    {
        self::assertSame(201, (new SamsungApiResponse(201, []))->getStatusCode());
    }

    public function testGetData(): void
    {
        $data = ['cardId' => 'test'];
        self::assertSame($data, (new SamsungApiResponse(200, $data))->getData());
    }
}
