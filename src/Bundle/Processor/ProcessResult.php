<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Processor;

use Jolicode\WalletKit\Bundle\Entity\PendingOperation;

/**
 * Per-operation outcome of a processor run. Operations listed in neither
 * collection should never occur — processors must account for every claim.
 */
final class ProcessResult
{
    /** @var PendingOperation[] operations completed successfully */
    private array $successes = [];

    /** @var array<int, array{operation: PendingOperation, error: string}> */
    private array $failures = [];

    public function addSuccess(PendingOperation $operation): void
    {
        $this->successes[] = $operation;
    }

    public function addFailure(PendingOperation $operation, string $error): void
    {
        $this->failures[] = ['operation' => $operation, 'error' => $error];
    }

    /**
     * @return PendingOperation[]
     */
    public function getSuccesses(): array
    {
        return $this->successes;
    }

    /**
     * @return array<int, array{operation: PendingOperation, error: string}>
     */
    public function getFailures(): array
    {
        return $this->failures;
    }
}
