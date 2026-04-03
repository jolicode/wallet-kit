<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType;

use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\SemanticLocation;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type SemanticLocationType from SemanticLocation
 */
class SemanticLocationNormalizer implements NormalizerInterface
{
    /**
     * @param SemanticLocation     $object
     * @param array<string, mixed> $context
     *
     * @return SemanticLocationType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return [
            'latitude' => $object->latitude,
            'longitude' => $object->longitude,
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof SemanticLocation;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [SemanticLocation::class => true];
    }
}
