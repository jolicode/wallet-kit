<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Processor;

use Jolicode\WalletKit\Api\Samsung\SamsungWalletClient;
use Jolicode\WalletKit\Bundle\WalletPlatformEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\Card;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class SamsungApiProcessor implements PendingOperationProcessorInterface
{
    public function __construct(
        private readonly SamsungWalletClient $client,
        private readonly DenormalizerInterface $denormalizer,
    ) {
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
                continue;
            }

            $operationType = (string) $payload['operationType'];

            match ($operationType) {
                'create' => $this->processCreate($payload),
                'update' => $this->processUpdate($payload),
                'change_state' => $this->processChangeState($payload),
                'push' => $this->processPush($payload),
                default => null,
            };
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
