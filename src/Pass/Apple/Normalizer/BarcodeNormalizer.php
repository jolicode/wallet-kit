<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Normalizer;

use Jolicode\WalletKit\Pass\Apple\Model\Barcode;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type BarcodeType from Barcode
 */
class BarcodeNormalizer implements NormalizerInterface
{
    /**
     * @param Barcode              $object
     * @param array<string, mixed> $context
     *
     * @return BarcodeType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return [
            'altText' => $object->altText,
            'format' => $object->format->value,
            'message' => $object->message,
            'messageEncoding' => $object->messageEncoding,
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Barcode;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [Barcode::class => true];
    }
}
