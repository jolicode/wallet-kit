<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Samsung\Model\Shared\Location;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type SamsungLocationType from Location
 */
class LocationNormalizer implements NormalizerInterface
{
    /**
     * @param Location             $object
     * @param array<string, mixed> $context
     *
     * @return SamsungLocationType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'lat' => $object->lat,
            'lng' => $object->lng,
        ];

        if (null !== $object->address) {
            $data['address'] = $object->address;
        }

        if (null !== $object->name) {
            $data['name'] = $object->name;
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
