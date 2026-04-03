<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Normalizer;

use Jolicode\WalletKit\Pass\Apple\Model\Location;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type LocationType from Location
 */
class LocationNormalizer implements NormalizerInterface
{
    /**
     * @param Location             $object
     * @param array<string, mixed> $context
     *
     * @return LocationType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'latitude' => $object->latitude,
            'longitude' => $object->longitude,
        ];

        if (null !== $object->altitude) {
            $data['altitude'] = $object->altitude;
        }

        if (null !== $object->relevantText) {
            $data['relevantText'] = $object->relevantText;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Location;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [Location::class => true];
    }
}
