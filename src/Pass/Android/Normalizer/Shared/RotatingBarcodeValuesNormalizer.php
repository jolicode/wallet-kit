<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\RotatingBarcodeValues;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type RotatingBarcodeValuesType from RotatingBarcodeValues
 */
class RotatingBarcodeValuesNormalizer implements NormalizerInterface
{
    /**
     * @param RotatingBarcodeValues $object
     * @param array<string, mixed>  $context
     *
     * @return RotatingBarcodeValuesType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->startDateTime) {
            $data['startDateTime'] = $object->startDateTime;
        }

        if (null !== $object->values) {
            $data['values'] = $object->values;
        }

        if (null !== $object->periodMillis) {
            $data['periodMillis'] = $object->periodMillis;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof RotatingBarcodeValues;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [RotatingBarcodeValues::class => true];
    }
}
