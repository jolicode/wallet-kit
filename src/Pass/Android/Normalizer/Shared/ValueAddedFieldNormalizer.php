<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\ValueAddedField;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type ValueAddedFieldType from ValueAddedField
 */
class ValueAddedFieldNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param ValueAddedField      $object
     * @param array<string, mixed> $context
     *
     * @return ValueAddedFieldType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->header) {
            $data['header'] = $object->header;
        }

        if (null !== $object->localizedHeader) {
            $data['localizedHeader'] = $this->normalizer->normalize($object->localizedHeader, $format, $context);
        }

        if (null !== $object->body) {
            $data['body'] = $object->body;
        }

        if (null !== $object->localizedBody) {
            $data['localizedBody'] = $this->normalizer->normalize($object->localizedBody, $format, $context);
        }

        if (null !== $object->actionUri) {
            $data['actionUri'] = $this->normalizer->normalize($object->actionUri, $format, $context);
        }

        if (null !== $object->image) {
            $data['image'] = $this->normalizer->normalize($object->image, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ValueAddedField;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [ValueAddedField::class => true];
    }
}
