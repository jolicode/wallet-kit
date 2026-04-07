<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\RotatingBarcode;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type RotatingBarcodeType from RotatingBarcode
 */
class RotatingBarcodeNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param RotatingBarcode      $object
     * @param array<string, mixed> $context
     *
     * @return RotatingBarcodeType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->type) {
            $data['type'] = $object->type->value;
        }

        if (null !== $object->renderEncoding) {
            $data['renderEncoding'] = $object->renderEncoding->value;
        }

        if (null !== $object->valuePattern) {
            $data['valuePattern'] = $object->valuePattern;
        }

        if (null !== $object->alternateText) {
            $data['alternateText'] = $object->alternateText;
        }

        if (null !== $object->showCodeText) {
            $data['showCodeText'] = $this->normalizer->normalize($object->showCodeText, $format, $context);
        }

        if (null !== $object->totpDetails) {
            $data['totpDetails'] = $this->normalizer->normalize($object->totpDetails, $format, $context);
        }

        if (null !== $object->values) {
            $data['values'] = $this->normalizer->normalize($object->values, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof RotatingBarcode;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [RotatingBarcode::class => true];
    }
}
