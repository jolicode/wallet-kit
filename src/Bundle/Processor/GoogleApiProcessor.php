<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Processor;

use Jolicode\WalletKit\Api\Google\GoogleWalletClient;
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Bundle\WalletPlatformEnum;
use Jolicode\WalletKit\Exception\Api\UnknownOperationTypeException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class GoogleApiProcessor implements PendingOperationProcessorInterface
{
    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly GoogleWalletClient $client,
        private readonly DenormalizerInterface $denormalizer,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
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
                $this->logger->warning('Skipping Google operation with missing operationType/pair.', ['operation_id' => $operation->id ?? null]);
                continue;
            }

            $operationType = (string) $payload['operationType'];

            try {
                /** @var GoogleWalletPair $pair */
                $pair = $this->denormalizer->denormalize($payload['pair'], GoogleWalletPair::class);

                match ($operationType) {
                    'create_or_update' => $this->client->createOrUpdatePass($pair),
                    'create_class' => $this->client->createClass($pair),
                    'update_class' => $this->client->updateClass($pair),
                    'create_object' => $this->client->createObject($pair),
                    'update_object' => $this->client->updateObject($pair),
                    default => throw new UnknownOperationTypeException($operationType),
                };
            } catch (\Throwable $e) {
                $this->logger->error('Google operation processing failed: {message}', [
                    'message' => $e->getMessage(),
                    'operation_type' => $operationType,
                    'exception' => $e,
                ]);
            }
        }
    }
}
