<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Processor;

use Jolicode\WalletKit\Api\Samsung\SamsungWalletClient;
use Jolicode\WalletKit\Bundle\WalletPlatformEnum;
use Jolicode\WalletKit\Exception\Api\UnknownOperationTypeException;
use Jolicode\WalletKit\Pass\Samsung\Model\Card;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class SamsungApiProcessor implements PendingOperationProcessorInterface
{
    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly SamsungWalletClient $client,
        private readonly DenormalizerInterface $denormalizer,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function supports(): WalletPlatformEnum
    {
        return WalletPlatformEnum::SAMSUNG;
    }

    public function process(array $operations): void
    {
        foreach ($operations as $operation) {
            $payload = $operation->payload;

            if (!\array_key_exists('operationType', $payload)) {
                $this->logger->warning('Skipping Samsung operation with missing operationType.');
                continue;
            }

            $operationType = (string) $payload['operationType'];

            try {
                match ($operationType) {
                    'create' => $this->processCreate($payload),
                    'update' => $this->processUpdate($payload),
                    'change_state' => $this->processChangeState($payload),
                    'push' => $this->processPush($payload),
                    default => throw new UnknownOperationTypeException($operationType),
                };
            } catch (\Throwable $e) {
                $this->logger->error('Samsung operation processing failed: {message}', [
                    'message' => $e->getMessage(),
                    'operation_type' => $operationType,
                    'exception' => $e,
                ]);
            }
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function processCreate(array $payload): void
    {
        if (!\array_key_exists('card', $payload)) {
            return;
        }

        /** @var Card $card */
        $card = $this->denormalizer->denormalize($payload['card'], Card::class);

        $this->client->createCard($card);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function processUpdate(array $payload): void
    {
        if (!\array_key_exists('card', $payload) || !\array_key_exists('cardId', $payload)) {
            return;
        }

        /** @var Card $card */
        $card = $this->denormalizer->denormalize($payload['card'], Card::class);

        $this->client->updateCard($card, (string) $payload['cardId']);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function processChangeState(array $payload): void
    {
        if (!\array_key_exists('cardId', $payload) || !\array_key_exists('state', $payload)) {
            return;
        }

        $this->client->updateCardState((string) $payload['cardId'], (string) $payload['state']);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function processPush(array $payload): void
    {
        if (!\array_key_exists('cardId', $payload)) {
            return;
        }

        $this->client->pushCardUpdate((string) $payload['cardId']);
    }
}
