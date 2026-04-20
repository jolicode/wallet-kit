<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Repository;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Jolicode\WalletKit\Bundle\Entity\PendingOperation;
use Jolicode\WalletKit\Bundle\Entity\PendingOperationStatusEnum;

final class DoctrinePendingOperationRepository implements PendingOperationRepositoryInterface
{
    private const TABLE = 'wallet_kit_pending_operation';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function enqueue(array $operations): void
    {
        foreach ($operations as $operation) {
            $this->entityManager->persist($operation);
        }

        $this->entityManager->flush();
    }

    public function dequeue(string $batchGroupId, int $limit): array
    {
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        return $connection->transactional(function () use ($connection, $platform, $batchGroupId, $limit): array {
            if ($platform instanceof PostgreSQLPlatform) {
                $lockSql = 'SELECT id FROM ' . self::TABLE
                    . ' WHERE batch_group_id = :batchGroupId AND status = :pending'
                    . ' ORDER BY id ASC LIMIT :limit FOR UPDATE SKIP LOCKED';
            } elseif ($platform instanceof MySQLPlatform) {
                $lockSql = 'SELECT id FROM ' . self::TABLE
                    . ' WHERE batch_group_id = :batchGroupId AND status = :pending'
                    . ' ORDER BY id ASC LIMIT :limit FOR UPDATE SKIP LOCKED';
            } else {
                // SQLite / unknown: rely on transaction serialization.
                $lockSql = 'SELECT id FROM ' . self::TABLE
                    . ' WHERE batch_group_id = :batchGroupId AND status = :pending'
                    . ' ORDER BY id ASC LIMIT :limit';
            }

            $ids = $connection->fetchFirstColumn($lockSql, [
                'batchGroupId' => $batchGroupId,
                'pending' => PendingOperationStatusEnum::PENDING->value,
                'limit' => $limit,
            ], [
                'limit' => \PDO::PARAM_INT,
            ]);

            if (0 === \count($ids)) {
                return [];
            }

            $connection->executeStatement(
                'UPDATE ' . self::TABLE
                . ' SET status = :processing, attempts = attempts + 1, processing_started_at = :now'
                . ' WHERE id IN (:ids)',
                [
                    'processing' => PendingOperationStatusEnum::PROCESSING->value,
                    'now' => new \DateTimeImmutable(),
                    'ids' => $ids,
                ],
                [
                    'now' => 'datetime_immutable',
                    'ids' => \Doctrine\DBAL\ArrayParameterType::INTEGER,
                ],
            );

            $this->entityManager->clear(PendingOperation::class);

            /** @var PendingOperation[] $operations */
            $operations = $this->entityManager->getRepository(PendingOperation::class)
                ->findBy(['id' => $ids], ['id' => 'ASC']);

            return $operations;
        });
    }

    public function markSuccess(array $operations): void
    {
        if (0 === \count($operations)) {
            return;
        }

        $ids = array_map(static fn (PendingOperation $op): ?int => $op->id, $operations);
        $ids = array_values(array_filter($ids, static fn (?int $id): bool => null !== $id));

        if (0 === \count($ids)) {
            return;
        }

        $this->entityManager->getConnection()->executeStatement(
            'DELETE FROM ' . self::TABLE . ' WHERE id IN (:ids)',
            ['ids' => $ids],
            ['ids' => \Doctrine\DBAL\ArrayParameterType::INTEGER],
        );
    }

    public function markFailed(array $operations, string $error, int $maxAttempts): void
    {
        if (0 === \count($operations)) {
            return;
        }

        $connection = $this->entityManager->getConnection();

        foreach ($operations as $operation) {
            if (null === $operation->id) {
                continue;
            }

            $status = $operation->attempts >= $maxAttempts
                ? PendingOperationStatusEnum::FAILED
                : PendingOperationStatusEnum::PENDING;

            $connection->executeStatement(
                'UPDATE ' . self::TABLE
                . ' SET status = :status, last_error = :error, processing_started_at = NULL'
                . ' WHERE id = :id',
                [
                    'status' => $status->value,
                    'error' => $error,
                    'id' => $operation->id,
                ],
            );
        }
    }

    public function resetStaleProcessing(\DateTimeImmutable $olderThan): int
    {
        return (int) $this->entityManager->getConnection()->executeStatement(
            'UPDATE ' . self::TABLE
            . ' SET status = :pending, processing_started_at = NULL'
            . ' WHERE status = :processing AND processing_started_at < :olderThan',
            [
                'pending' => PendingOperationStatusEnum::PENDING->value,
                'processing' => PendingOperationStatusEnum::PROCESSING->value,
                'olderThan' => $olderThan,
            ],
            ['olderThan' => 'datetime_immutable'],
        );
    }

    public function countPending(string $batchGroupId): int
    {
        return (int) $this->entityManager->getConnection()->fetchOne(
            'SELECT COUNT(id) FROM ' . self::TABLE
            . ' WHERE batch_group_id = :batchGroupId AND status = :pending',
            [
                'batchGroupId' => $batchGroupId,
                'pending' => PendingOperationStatusEnum::PENDING->value,
            ],
        );
    }
}
