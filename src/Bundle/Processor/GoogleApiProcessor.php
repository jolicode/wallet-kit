<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Processor;

use Jolicode\WalletKit\Api\Google\GoogleWalletClient;
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Bundle\WalletPlatformEnum;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class GoogleApiProcessor implements PendingOperationProcessorInterface
{
    public function __construct(
        private readonly GoogleWalletClient $client,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function supports(): WalletPlatformEnum
    {
        return WalletPlatformEnum::GOOGLE;
    }

    public function process(array $operations): void
    {
        foreach ($operations as $operation) {
            $payload = $operation->payload;

            if (!\array_key_exists('operationType', $payload) || !\array_key_exists('pair', $payload)) {
                continue;
            }

            $operationType = (string) $payload['operationType'];

            /** @var GoogleWalletPair $pair */
            $pair = $this->denormalizer->denormalize($payload['pair'], GoogleWalletPair::class);

            match ($operationType) {
                'create_or_update' => $this->client->createOrUpdatePass($pair),
                'create_class' => $this->client->createClass($pair),
                'update_class' => $this->client->updateClass($pair),
                'create_object' => $this->client->createObject($pair),
                'update_object' => $this->client->updateObject($pair),
                default => null,
            };
        }
    }
}
