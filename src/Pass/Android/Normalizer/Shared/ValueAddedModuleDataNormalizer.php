<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\ValueAddedModuleData;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type ValueAddedModuleDataType from ValueAddedModuleData
 */
class ValueAddedModuleDataNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param ValueAddedModuleData $object
     * @param array<string, mixed> $context
     *
     * @return ValueAddedModuleDataType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->id) {
            $data['id'] = $object->id;
        }

        if (null !== $object->totalValue) {
            $data['totalValue'] = $this->normalizer->normalize($object->totalValue, $format, $context);
        }

        if (null !== $object->fields) {
            $fields = [];
            foreach ($object->fields as $field) {
                $fields[] = $this->normalizer->normalize($field, $format, $context);
            }
            $data['fields'] = $fields;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ValueAddedModuleData;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [ValueAddedModuleData::class => true];
    }
}
