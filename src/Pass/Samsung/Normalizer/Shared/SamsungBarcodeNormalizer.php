<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungBarcode;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type SamsungBarcodeType from SamsungBarcode
 */
class SamsungBarcodeNormalizer implements NormalizerInterface
{
    /**
     * @param SamsungBarcode       $object
     * @param array<string, mixed> $context
     *
     * @return SamsungBarcodeType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'serialType' => $object->serialType->value,
        ];

        if (null !== $object->value) {
            $data['value'] = $object->value;
        }

        if (null !== $object->ptFormat) {
            $data['ptFormat'] = $object->ptFormat;
        }

        if (null !== $object->ptSubFormat) {
            $data['ptSubFormat'] = $object->ptSubFormat;
        }

        if (null !== $object->pin) {
            $data['pin'] = $object->pin;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof SamsungBarcode;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [SamsungBarcode::class => true];
    }
}
