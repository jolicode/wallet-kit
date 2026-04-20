<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Exception\Api;

use Jolicode\WalletKit\Exception\Api\ApiResponseException;
use Jolicode\WalletKit\Exception\Api\AuthenticationException;
use Jolicode\WalletKit\Exception\Api\GoogleServiceAccountException;
use Jolicode\WalletKit\Exception\Api\HttpRequestException;
use Jolicode\WalletKit\Exception\Api\MissingExtensionException;
use Jolicode\WalletKit\Exception\Api\PackagingException;
use Jolicode\WalletKit\Exception\Api\RateLimitException;
use Jolicode\WalletKit\Exception\WalletKitException;
use PHPUnit\Framework\TestCase;

final class ExceptionHierarchyTest extends TestCase
{
    public function testAllImplementWalletKitException(): void
    {
        $exceptions = [
            new AuthenticationException('auth failed'),
            new HttpRequestException('transport error'),
            new ApiResponseException(500, '{"error":"internal"}'),
            new RateLimitException('{"error":"rate limit"}', 30),
            new PackagingException('signing failed'),
            new MissingExtensionException('ext-openssl required'),
            new GoogleServiceAccountException('invalid service account JSON'),
        ];

        foreach ($exceptions as $exception) {
            self::assertInstanceOf(WalletKitException::class, $exception, $exception::class . ' must implement WalletKitException');
        }
    }

    public function testAuthenticationExceptionExtendsRuntimeException(): void
    {
        self::assertInstanceOf(\RuntimeException::class, new AuthenticationException('test'));
    }

    public function testHttpRequestExceptionWrapsTransportError(): void
    {
        $previous = new \RuntimeException('connection refused');
        $exception = new HttpRequestException('transport error', $previous);

        self::assertSame('transport error', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testApiResponseExceptionStoresStatusCodeAndBody(): void
    {
        $exception = new ApiResponseException(404, '{"error":"not found"}');

        self::assertSame(404, $exception->statusCode);
        self::assertSame('{"error":"not found"}', $exception->responseBody);
        self::assertSame(404, $exception->getCode());
        self::assertStringContainsString('404', $exception->getMessage());
    }

    public function testRateLimitExceptionExtendsApiResponseException(): void
    {
        $exception = new RateLimitException('{"error":"too many"}', 60);

        self::assertInstanceOf(ApiResponseException::class, $exception);
        self::assertSame(429, $exception->statusCode);
        self::assertSame(60, $exception->retryAfterSeconds);
        self::assertSame('{"error":"too many"}', $exception->responseBody);
    }

    public function testRateLimitExceptionWithNullRetryAfter(): void
    {
        $exception = new RateLimitException('{}');

        self::assertNull($exception->retryAfterSeconds);
    }

    public function testPackagingExceptionExtendsRuntimeException(): void
    {
        self::assertInstanceOf(\RuntimeException::class, new PackagingException('signing failed'));
    }

    public function testMissingExtensionExceptionExtendsLogicException(): void
    {
        self::assertInstanceOf(\LogicException::class, new MissingExtensionException('ext-openssl'));
    }

    public function testGoogleServiceAccountExceptionExtendsRuntimeException(): void
    {
        self::assertInstanceOf(\RuntimeException::class, new GoogleServiceAccountException('read failed'));
    }
}
