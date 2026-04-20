<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Processor;

use Jolicode\WalletKit\Api\Apple\ApplePushNotifier;
use Jolicode\WalletKit\Bundle\Repository\PassRegistrationRepositoryInterface;
use Jolicode\WalletKit\Bundle\WalletPlatformEnum;

final class ApplePushProcessor implements PendingOperationProcessorInterface
{
    public function __construct(
        private readonly ApplePushNotifier $pushNotifier,
        private readonly PassRegistrationRepositoryInterface $registrationRepository,
    ) {
    }

    public function supports(): WalletPlatformEnum
    {
        return WalletPlatformEnum::APPLE;
    }

    public function process(array $operations): void
    {
        /** @var array<string, string> $tokenToPassType pushToken => passTypeId */
        $tokenToPassType = [];

        foreach ($operations as $operation) {
            $payload = $operation->payload;

            if (\array_key_exists('pushToken', $payload) && \array_key_exists('passTypeId', $payload)) {
                $tokenToPassType[(string) $payload['pushToken']] = (string) $payload['passTypeId'];
            }
        }

        if (0 === \count($tokenToPassType)) {
            return;
        }

        // Group by passTypeId for batch sending
        /** @var array<string, list<string>> $grouped passTypeId => pushTokens */
        $grouped = [];
        foreach ($tokenToPassType as $pushToken => $passTypeId) {
            $grouped[$passTypeId][] = $pushToken;
        }

        foreach ($grouped as $passTypeId => $pushTokens) {
            $responses = $this->pushNotifier->sendBatchUpdateNotifications($pushTokens, $passTypeId);

            foreach ($responses as $response) {
                if ($response->isDeviceTokenInactive()) {
                    $this->registrationRepository->unregisterByPushToken($response->getPushToken());
                }
            }
        }
    }
}
