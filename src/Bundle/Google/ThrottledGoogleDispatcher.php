<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Google;

use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Bundle\Entity\PendingOperation;
use Jolicode\WalletKit\Bundle\Messenger\ProcessPendingOperationsMessage;
use Jolicode\WalletKit\Bundle\Repository\PendingOperationRepositoryInterface;
use Jolicode\WalletKit\Bundle\WalletPlatformEnum;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ThrottledGoogleDispatcher
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly PendingOperationRepositoryInterface $pendingOperationRepository,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    /**
     * @param GoogleWalletPair[] $pairs
     */
    public function dispatchBulkCreateOrUpdate(array $pairs): string
    {
        return $this->dispatchBulkOperation($pairs, 'create_or_update');
    }

    /**
     * @param GoogleWalletPair[] $pairs
     */
    public function dispatchBulkOperation(array $pairs, string $operationType): string
    {
        $batchGroupId = bin2hex(random_bytes(16));

        $operations = [];
        foreach ($pairs as $pair) {
            $operations[] = new PendingOperation(
                WalletPlatformEnum::GOOGLE,
                $batchGroupId,
                [
                    'operationType' => $operationType,
                    'pair' => $this->normalizer->normalize($pair),
                ],
            );
        }

        $this->pendingOperationRepository->enqueue($operations);

        $this->messageBus->dispatch(
            new ProcessPendingOperationsMessage(WalletPlatformEnum::GOOGLE, $batchGroupId),
        );

        return $batchGroupId;
    }
}
