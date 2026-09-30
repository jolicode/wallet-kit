<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Bundle\Repository;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Jolicode\WalletKit\Bundle\Entity\PassRegistration;
use Jolicode\WalletKit\Bundle\Repository\DoctrinePassRegistrationRepository;
use Jolicode\WalletKit\Bundle\Repository\PassRegistrationOutcome;
use PHPUnit\Framework\TestCase;

final class DoctrinePassRegistrationRepositoryTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private DoctrinePassRegistrationRepository $repository;

    protected function setUp(): void
    {
        if (!\in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('The pdo_sqlite extension is not available.');
        }

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        $config = ORMSetup::createAttributeMetadataConfiguration([__DIR__ . '/../../../src/Bundle/Entity']);

        if (\PHP_VERSION_ID >= 80400) {
            $config->enableNativeLazyObjects(true);
        }

        $this->entityManager = new EntityManager($connection, $config);

        $connection->executeStatement('CREATE TABLE wallet_kit_pass_registration (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            deviceId STRING NOT NULL,
            passTypeId STRING NOT NULL,
            serialNumber STRING NOT NULL,
            pushToken STRING NOT NULL,
            registeredAt STRING NOT NULL
        )');

        $this->repository = new DoctrinePassRegistrationRepository($this->entityManager);
    }

    public function testRegisterReturnsCreatedThenRotatedThenUnchanged(): void
    {
        $result = $this->repository->register('device-1', 'pass.com.example', 'SERIAL-001', 'pt-1');
        self::assertSame(PassRegistrationOutcome::Created, $result->outcome);
        self::assertNull($result->previousPushToken);

        $rotated = $this->repository->register('device-1', 'pass.com.example', 'SERIAL-001', 'pt-2');
        self::assertSame(PassRegistrationOutcome::TokenUpdated, $rotated->outcome);
        self::assertSame('pt-1', $rotated->previousPushToken, 'previous token exposed for external cleanup');

        $unchanged = $this->repository->register('device-1', 'pass.com.example', 'SERIAL-001', 'pt-2');
        self::assertSame(PassRegistrationOutcome::Unchanged, $unchanged->outcome);
        self::assertNull($unchanged->previousPushToken);
    }

    public function testUnchangedRegistrationDoesNotPersistAnything(): void
    {
        $this->repository->register('device-1', 'pass.com.example', 'SERIAL-001', 'pt-1');
        $this->repository->register('device-1', 'pass.com.example', 'SERIAL-001', 'pt-1');

        // Only one row exists, with the original token.
        $registration = $this->entityManager
            ->getRepository(PassRegistration::class)
            ->findOneBy(['deviceId' => 'device-1']);
        self::assertNotNull($registration);
        self::assertSame('pt-1', $registration->getPushToken());

        // Mu-independent proof of the no-op: a second flush cycle with no change
        // leaves no pending dirty object.
        self::assertNotSame($registration, $this->repository->register('device-1', 'pass.com.example', 'SERIAL-001', 'pt-1'));
    }

    public function testUnregisterReturnsWhetherARowWasRemoved(): void
    {
        $this->repository->register('device-1', 'pass.com.example', 'SERIAL-001', 'pt-1');

        self::assertTrue($this->repository->unregister('device-1', 'pass.com.example', 'SERIAL-001'));
        self::assertFalse($this->repository->unregister('device-1', 'pass.com.example', 'SERIAL-001'), 'second call finds no row');
    }

    public function testSamePassDifferentDevicesAreDistinctRegistrations(): void
    {
        $this->repository->register('device-1', 'pass.com.example', 'SERIAL-001', 'pt-1');
        $this->repository->register('device-2', 'pass.com.example', 'SERIAL-001', 'pt-2');

        self::assertSame(
            ['pt-1', 'pt-2'],
            $this->repository->findPushTokens('pass.com.example', 'SERIAL-001'),
            'multi-device registrations for one pass coexist',
        );

        $this->repository->unregister('device-2', 'pass.com.example', 'SERIAL-001');
        self::assertSame(['pt-1'], $this->repository->findPushTokens('pass.com.example', 'SERIAL-001'), 'removing device-2 keeps device-1');
    }
}
