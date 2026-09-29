<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Messenger;

use Jolicode\WalletKit\Bundle\Processor\PendingOperationProcessorInterface;
use Jolicode\WalletKit\Bundle\Repository\PendingOperationRepositoryInterface;
use Jolicode\WalletKit\Exception\Api\RateLimitException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
final class ProcessPendingOperationsHandler
{
    private const MAX_ATTEMPTS = 5;

    /** @var array<string, PendingOperationProcessorInterface> */
    private readonly array $processorMap;

    /**
     * @param iterable<PendingOperationProcessorInterface> $processors
     * @param array<string, array<string, int>>            $batchConfig
     */
    public function __construct(
        private readonly PendingOperationRepositoryInterface $repository,
        private readonly MessageBusInterface $messageBus,
        iterable $processors,
        private readonly array $batchConfig,
        ?LoggerInterface $logger = null,
    ) {
        $map = [];
        foreach ($processors as $processor) {
            $map[$processor->supports()->value] = $processor;
        }
        $this->processorMap = $map;
        $this->logger = $logger ?? new NullLogger();
    }

    private readonly LoggerInterface $logger;

    public function __invoke(ProcessPendingOperationsMessage $message): void
    {
        $platformValue = $message->platform->value;

        if (!\array_key_exists($platformValue, $this->processorMap)) {
            return;
        }

        $processor = $this->processorMap[$platformValue];

        $batchSize = 50;
        $batchInterval = 1;

        if (\array_key_exists($platformValue, $this->batchConfig)) {
            $config = $this->batchConfig[$platformValue];

            $batchSizeKey = 'apple' === $platformValue ? 'pushBatchSize' : 'apiBatchSize';
            $batchIntervalKey = 'apple' === $platformValue ? 'pushBatchInterval' : 'apiBatchInterval';

            if (\array_key_exists($batchSizeKey, $config)) {
                $batchSize = (int) $config[$batchSizeKey];
            }
            if (\array_key_exists($batchIntervalKey, $config)) {
                $batchInterval = (int) $config[$batchIntervalKey];
            }
        }

        $this->repository->resetStaleProcessing(new \DateTimeImmutable('-10 minutes'));

        $operations = $this->repository->dequeue($message->batchGroupId, $batchSize);

        if (0 === \count($operations)) {
            return;
        }

        try {
            $result = $processor->process($operations);
        } catch (RateLimitException $e) {
            // Platform throttling: put the whole batch back and retry after a delay.
            $this->repository->markFailed($operations, 'rate-limited', \PHP_INT_MAX);
            $retryAfter = $e->retryAfterSeconds ?? $batchInterval;

            $this->messageBus->dispatch(
                new ProcessPendingOperationsMessage($message->platform, $message->batchGroupId),
                [new DelayStamp($retryAfter * 1000)],
            );

            return;
        }

        // Operations are accounted individually: successes are removed, failures
        // go back to pending (or failed once attempts cap) with their own error.
        $this->repository->markSuccess($result->getSuccesses());

        foreach ($result->getFailures() as ['operation' => $operation, 'error' => $error]) {
            $this->repository->markFailed([$operation], $error, self::MAX_ATTEMPTS);
        }
        $failedCount = \count($result->getFailures());

        if ($failedCount > 0) {
            $this->logger->error(
                \sprintf('Failed to process %d pending wallet operation(s) for %s.', $failedCount, $platformValue),
            );
        }

        $remaining = $this->repository->countPending($message->batchGroupId);

        if ($remaining > 0) {
            $this->messageBus->dispatch(
                new ProcessPendingOperationsMessage($message->platform, $message->batchGroupId),
                [new DelayStamp($batchInterval * 1000)],
            );
        }
    }
}
