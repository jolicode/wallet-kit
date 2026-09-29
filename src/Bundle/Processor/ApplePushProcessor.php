<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Processor;

use Jolicode\WalletKit\Api\Apple\ApplePushNotifier;
use Jolicode\WalletKit\Bundle\Entity\PendingOperation;
use Jolicode\WalletKit\Bundle\Repository\PassRegistrationRepositoryInterface;
use Jolicode\WalletKit\Bundle\WalletPlatformEnum;
use Jolicode\WalletKit\Exception\Api\RateLimitException;

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

    public function process(array $operations): ProcessResult
    {
        $result = new ProcessResult();

        /** @var array<string, PendingOperation> $tokenToOperation pushToken => originating operation */
        $tokenToOperation = [];
        /** @var array<string, list<string>> $grouped passTypeId => pushTokens */
        $grouped = [];

        foreach ($operations as $operation) {
            $payload = $operation->payload;

            if (!\array_key_exists('pushToken', $payload) || !\array_key_exists('passTypeId', $payload)) {
                $result->addFailure($operation, 'Missing "pushToken" or "passTypeId" in Apple push payload.');

                continue;
            }

            $pushToken = (string) $payload['pushToken'];

            if (\array_key_exists($pushToken, $tokenToOperation)) {
                continue; // duplicate token in batch; the first occurrence drives the APNs call
            }

            $tokenToOperation[$pushToken] = $operation;
            $grouped[(string) $payload['passTypeId']][] = $pushToken;
        }

        foreach ($grouped as $passTypeId => $pushTokens) {
            $responses = $this->pushNotifier->sendBatchUpdateNotifications($pushTokens, $passTypeId);

            foreach ($responses as $response) {
                $operation = $tokenToOperation[$response->getPushToken()] ?? null;

                if (null === $operation) {
                    continue;
                }

                if ($response->isDeviceTokenInactive()) {
                    // 410: Apple asks us to stop pushing — drop the (stale) registration.
                    $this->registrationRepository->unregisterByPushToken($response->getPushToken());
                    $result->addSuccess($operation);
                } elseif ($response->isRateLimited()) {
                    // APNs throttling: hand the whole batch back with the rate-limit
                    // delay path instead of silently reporting success.
                    throw RateLimitException::withoutRetryAfter(\sprintf('APNs rate-limited update for pass type "%s".', $passTypeId));
                } elseif ($response->isSuccessful()) {
                    $result->addSuccess($operation);
                } else {
                    $responseError = $response->getErrorReason() ?? 'unknown reason';
                    $result->addFailure($operation, \sprintf('APNs rejected push (HTTP %d): %s', $response->getStatusCode(), $responseError));
                }
            }
        }

        return $result;
    }
}
