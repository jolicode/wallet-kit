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
     * @return PendingOperation[]
     */
    public function dequeue(string $batchGroupId, int $limit): array;

    public function countPending(string $batchGroupId): int;
}
