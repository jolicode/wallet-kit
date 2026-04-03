<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Normalizer;

use Jolicode\WalletKit\Pass\Apple\Model\RelevantDate;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type RelevantDateType from RelevantDate
 */
class RelevantDateNormalizer implements NormalizerInterface
{
    /**
     * @param RelevantDate         $object
     * @param array<string, mixed> $context
     *
     * @return RelevantDateType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return [
            'startDate' => $object->startDate,
            'endDate' => $object->endDate,
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof RelevantDate;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [RelevantDate::class => true];
    }
}
