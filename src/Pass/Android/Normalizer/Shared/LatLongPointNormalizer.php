<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\LatLongPoint;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type LatLongPointType from LatLongPoint
 */
class LatLongPointNormalizer implements NormalizerInterface
{
    /**
     * @param LatLongPoint         $object
     * @param array<string, mixed> $context
     *
     * @return LatLongPointType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->latitude) {
            $data['latitude'] = $object->latitude;
        }

        if (null !== $object->longitude) {
            $data['longitude'] = $object->longitude;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof LatLongPoint;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [LatLongPoint::class => true];
    }
}
