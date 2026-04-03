<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\Barcode;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type GoogleBarcodeType from Barcode
 */
class BarcodeNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param Barcode              $object
     * @param array<string, mixed> $context
     *
     * @return GoogleBarcodeType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->type) {
            $data['type'] = $object->type->value;
        }

        if (null !== $object->value) {
            $data['value'] = $object->value;
        }

        if (null !== $object->alternateText) {
            $data['alternateText'] = $object->alternateText;
        }

        if (null !== $object->renderEncoding) {
            $data['renderEncoding'] = $object->renderEncoding->value;
        }

        if (null !== $object->showCodeText) {
            $data['showCodeText'] = $this->normalizer->normalize($object->showCodeText, $format, $context);
        }

        return $data;
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
