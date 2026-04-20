<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Samsung;

use Jolicode\WalletKit\Bundle\Entity\PendingOperation;
use Jolicode\WalletKit\Bundle\Messenger\ProcessPendingOperationsMessage;
use Jolicode\WalletKit\Bundle\Repository\PendingOperationRepositoryInterface;
use Jolicode\WalletKit\Bundle\WalletPlatformEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\Card;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ThrottledSamsungDispatcher
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly PendingOperationRepositoryInterface $pendingOperationRepository,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    /**
     * @param Card[] $cards
     */
    public function dispatchBulkCreate(array $cards): string
    {
        $batchGroupId = bin2hex(random_bytes(16));

        $operations = [];
        foreach ($cards as $card) {
            $operations[] = new PendingOperation(
                WalletPlatformEnum::SAMSUNG,
                $batchGroupId,
                [
                    'operationType' => 'create',
                    'card' => $this->normalizer->normalize($card),
                ],
            );
        }

        $this->pendingOperationRepository->enqueue($operations);

        $this->messageBus->dispatch(
            new ProcessPendingOperationsMessage(WalletPlatformEnum::SAMSUNG, $batchGroupId),
        );

        return $batchGroupId;
    }

    /**
     * @param array<string, Card> $cards cardId => Card
     */
    public function dispatchBulkUpdate(array $cards): string
    {
        $batchGroupId = bin2hex(random_bytes(16));

        $operations = [];
        foreach ($cards as $cardId => $card) {
            $operations[] = new PendingOperation(
                WalletPlatformEnum::SAMSUNG,
                $batchGroupId,
                [
                    'operationType' => 'update',
                    'cardId' => $cardId,
                    'card' => $this->normalizer->normalize($card),
                ],
            );
        }

        $this->pendingOperationRepository->enqueue($operations);

        $this->messageBus->dispatch(
            new ProcessPendingOperationsMessage(WalletPlatformEnum::SAMSUNG, $batchGroupId),
        );

        return $batchGroupId;
    }
}
