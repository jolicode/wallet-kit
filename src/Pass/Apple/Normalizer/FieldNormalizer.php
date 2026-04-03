<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Normalizer;

use Jolicode\WalletKit\Pass\Apple\Model\Field;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type FieldType from Field
 */
class FieldNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param Field                $object
     * @param array<string, mixed> $context
     *
     * @return FieldType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'key' => $object->key,
            'value' => $object->value,
        ];

        if (null !== $object->label) {
            $data['label'] = $object->label;
        }

        if (null !== $object->changeMessage) {
            $data['changeMessage'] = $object->changeMessage;
        }

        if (null !== $object->textAlignment) {
            $data['textAlignment'] = $object->textAlignment->value;
        }

        if (null !== $object->attributedValue) {
            $data['attributedValue'] = $object->attributedValue;
        }

        if (null !== $object->dateStyle) {
            $data['dateStyle'] = $object->dateStyle->value;
        }

        if (null !== $object->timeStyle) {
            $data['timeStyle'] = $object->timeStyle->value;
        }

        if (null !== $object->isRelative) {
            $data['isRelative'] = $object->isRelative;
        }

        if (null !== $object->currencyCode) {
            $data['currencyCode'] = $object->currencyCode;
        }

        if (null !== $object->numberStyle) {
            $data['numberStyle'] = $object->numberStyle->value;
        }

        if (null !== $object->row) {
            $data['row'] = $object->row;
        }

        if (null !== $object->semantics) {
            $data['semantics'] = $this->normalizer->normalize($object->semantics, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Field;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [Field::class => true];
    }
}
