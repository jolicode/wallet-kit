<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Normalizer;

use Jolicode\WalletKit\Pass\Apple\Model\PassStructure;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type PassStructureType from PassStructure
 */
class PassStructureNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param PassStructure        $object
     * @param array<string, mixed> $context
     *
     * @return PassStructureType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->transitType) {
            $data['transitType'] = $object->transitType->value;
        }

        $this->normalizeFieldGroup($data, 'headerFields', $object->headerFields, $format, $context);
        $this->normalizeFieldGroup($data, 'primaryFields', $object->primaryFields, $format, $context);
        $this->normalizeFieldGroup($data, 'secondaryFields', $object->secondaryFields, $format, $context);
        $this->normalizeFieldGroup($data, 'auxiliaryFields', $object->auxiliaryFields, $format, $context);
        $this->normalizeFieldGroup($data, 'backFields', $object->backFields, $format, $context);
        $this->normalizeFieldGroup($data, 'additionalInfoFields', $object->additionalInfoFields, $format, $context);

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<mixed>         $fields
     * @param array<string, mixed> $context
     */
    private function normalizeFieldGroup(array &$data, string $key, array $fields, ?string $format, array $context): void
    {
        if ([] === $fields) {
            return;
        }

        $normalized = [];
        foreach ($fields as $field) {
            $normalized[] = $this->normalizer->normalize($field, $format, $context);
        }

        $data[$key] = $normalized;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof PassStructure;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [PassStructure::class => true];
    }
}
