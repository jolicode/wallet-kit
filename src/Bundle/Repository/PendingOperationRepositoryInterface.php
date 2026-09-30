<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Repository;

use Jolicode\WalletKit\Bundle\Entity\PendingOperation;

interface PendingOperationRepositoryInterface
{
    /**
     * @param PendingOperation[] $operations
     */
    public function enqueue(array $operations): void;

    /**
     * Atomically claims up to $limit pending operations for processing.
     *
     * Uses a database-specific locking strategy (FOR UPDATE SKIP LOCKED on Postgres/MySQL)
     * to ensure no two workers claim the same rows. Claimed rows are marked as
     * "processing" with attempts incremented and processingStartedAt set.
     *
     * @return PendingOperation[]
     */
    public function dequeue(string $batchGroupId, int $limit): array;

    /**
     * Permanently removes successfully processed operations.
     *
     * @param PendingOperation[] $operations
     */
    public function markSuccess(array $operations): void;

    /**
     * Marks operations as pending again (for retry) or failed (if max attempts reached).
     *
     * @param PendingOperation[] $operations
     */
    public function markFailed(array $operations, string $error, int $maxAttempts): void;

    /**
     * Resets "processing" operations that have been stuck longer than $olderThan
     * back to "pending" so they can be retried. Used to recover from crashed workers.
     */
    public function resetStaleProcessing(\DateTimeImmutable $olderThan): int;

    public function countPending(string $batchGroupId): int;
}
