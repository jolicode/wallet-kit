<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Push;

use Jolicode\WalletKit\Bundle\Entity\PendingOperation;
use Jolicode\WalletKit\Bundle\Messenger\ProcessPendingOperationsMessage;
use Jolicode\WalletKit\Bundle\Repository\PassRegistrationRepositoryInterface;
use Jolicode\WalletKit\Bundle\Repository\PendingOperationRepositoryInterface;
use Jolicode\WalletKit\Bundle\WalletPlatformEnum;
use Symfony\Component\Messenger\MessageBusInterface;

final class ThrottledPushDispatcher
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly PendingOperationRepositoryInterface $pendingOperationRepository,
        private readonly PassRegistrationRepositoryInterface $registrationRepository,
    ) {
    }

    public function dispatchUpdateNotifications(string $passTypeId, string $serialNumber): int
    {
        $pushTokens = $this->registrationRepository->findPushTokens($passTypeId, $serialNumber);

        if (0 === \count($pushTokens)) {
            return 0;
        }

        return $this->dispatchForTokens($pushTokens, $passTypeId, $serialNumber);
    }

    /**
     * @param string[] $pushTokens
     */
    public function dispatchForTokens(array $pushTokens, string $passTypeId, string $serialNumber): int
    {
        if (0 === \count($pushTokens)) {
            return 0;
        }

        $batchGroupId = bin2hex(random_bytes(16));

        $operations = [];
        foreach ($pushTokens as $pushToken) {
            $operations[] = new PendingOperation(
                WalletPlatformEnum::APPLE,
                $batchGroupId,
                [
                    'pushToken' => $pushToken,
                    'passTypeId' => $passTypeId,
                    'serialNumber' => $serialNumber,
                ],
            );
        }

        $this->pendingOperationRepository->enqueue($operations);

        $this->messageBus->dispatch(
            new ProcessPendingOperationsMessage(WalletPlatformEnum::APPLE, $batchGroupId),
        );

        return \count($operations);
    }
}
