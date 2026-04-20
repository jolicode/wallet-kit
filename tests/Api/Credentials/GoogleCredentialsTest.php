<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Credentials;

use Jolicode\WalletKit\Api\Credentials\GoogleCredentials;
use Jolicode\WalletKit\Exception\Api\GoogleServiceAccountException;
use PHPUnit\Framework\TestCase;

final class GoogleCredentialsTest extends TestCase
{
    public function testConstruct(): void
    {
        $credentials = new GoogleCredentials(
            serviceAccountJsonPath: '/path/to/sa.json',
        );

        self::assertSame('/path/to/sa.json', $credentials->serviceAccountJsonPath);
    }

    public function testGetServiceAccountDataLazyLoads(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'wallet_kit_test_');
        self::assertNotFalse($tmpFile);

        try {
            $expectedData = ['type' => 'service_account', 'project_id' => 'test-project'];
            file_put_contents($tmpFile, json_encode($expectedData, \JSON_THROW_ON_ERROR));

            $credentials = new GoogleCredentials(serviceAccountJsonPath: $tmpFile);

            $data = $credentials->getServiceAccountData();
            self::assertSame($expectedData, $data);

            // Second call returns cached value (same reference)
            self::assertSame($data, $credentials->getServiceAccountData());
        } finally {
            @unlink($tmpFile);
        }
    }

    public function testGetServiceAccountDataThrowsOnMissingFile(): void
    {
        $credentials = new GoogleCredentials(serviceAccountJsonPath: '/nonexistent/file.json');

        $this->expectException(GoogleServiceAccountException::class);
        $this->expectExceptionMessage('Unable to read service account JSON file');
        $credentials->getServiceAccountData();
    }

    public function testGetServiceAccountDataThrowsOnInvalidJson(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'wallet_kit_test_');
        self::assertNotFalse($tmpFile);

        try {
            file_put_contents($tmpFile, '{not json');

            $credentials = new GoogleCredentials(serviceAccountJsonPath: $tmpFile);

            $this->expectException(GoogleServiceAccountException::class);
            $this->expectExceptionMessage('Invalid service account JSON');
            $credentials->getServiceAccountData();
        } finally {
            @unlink($tmpFile);
        }
    }
}
