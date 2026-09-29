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

    public function process(array $operations): ProcessResult
    {
        $result = new ProcessResult();

        foreach ($operations as $operation) {
            try {
                $this->processOperation($operation);
                $result->addSuccess($operation);
            } catch (\Throwable $e) {
                // Poison pills stay visible: corrupt payloads / typo'd operation
                // types are recorded as failures, never silently dropped.
                $this->logger->error('Samsung operation processing failed.', [
                    'operation_id' => $operation->id,
                    'exception' => $e,
                ]);
                $result->addFailure($operation, $e->getMessage());
            }
        }

        return $result;
    }

    private function processOperation(PendingOperation $operation): void
    {
        $payload = $operation->payload;

        if (!\array_key_exists('operationType', $payload)) {
            throw new \InvalidArgumentException('Missing "operationType" in Samsung operation payload.');
        }

        $operationType = (string) $payload['operationType'];

        match ($operationType) {
            'create' => $this->require($payload, ['card'], $operationType, fn (array $payload): string => $this->client->createCard($this->denormalizeCard($payload['card']))),
            'update' => $this->require($payload, ['card', 'cardId'], $operationType, fn (array $payload): string => $this->client->updateCard($this->denormalizeCard($payload['card']), (string) $payload['cardId'])),
            'push' => $this->require($payload, ['cardId', 'eventId', 'type'], $operationType, fn (array $payload): string => $this->client->pushCardUpdate((string) $payload['cardId'], (string) $payload['eventId'], (string) $payload['type'])),
            default => throw new UnknownOperationTypeException($operationType),
        };
    }

    /**
     * @param array<string, mixed>                   $payload
     * @param list<string>                           $requiredKeys
     * @param callable(array<string, mixed>): mixed  $fn
     */
    private function require(array $payload, array $requiredKeys, string $operationType, callable $fn): void
    {
        foreach ($requiredKeys as $key) {
            if (!\array_key_exists($key, $payload)) {
                throw new \InvalidArgumentException(\sprintf('Missing "%s" in Samsung "%s" operation payload.', $key, $operationType));
            }
        }

        $fn($payload);
    }

    private function denormalizeCard(mixed $cardPayload): Card
    {
        /** @var Card $card */
        $card = $this->denormalizer->denormalize($cardPayload, Card::class);

        return $card;
    }
}
