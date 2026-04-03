<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Normalizer;

use Jolicode\WalletKit\Pass\Apple\Model\Beacon;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type BeaconType from Beacon
 */
class BeaconNormalizer implements NormalizerInterface
{
    /**
     * @param Beacon               $object
     * @param array<string, mixed> $context
     *
     * @return BeaconType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'proximityUUID' => $object->proximityUUID,
        ];

        if (null !== $object->major) {
            $data['major'] = $object->major;
        }

        if (null !== $object->minor) {
            $data['minor'] = $object->minor;
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
        return $data instanceof Beacon;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [Beacon::class => true];
    }
}
