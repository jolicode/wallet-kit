<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Normalizer;

use Jolicode\WalletKit\Pass\Samsung\Model\CardData;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type CardDataType from CardData
 */
class CardDataNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param CardData             $object
     * @param array<string, mixed> $context
     *
     * @return CardDataType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'refId' => $object->refId,
            'createdAt' => $object->createdAt,
            'updatedAt' => $object->updatedAt,
            'language' => $object->language,
            'attributes' => $this->normalizer->normalize($object->attributes, $format, $context),
        ];

        if (null !== $object->localization) {
            $localization = [];
            foreach ($object->localization as $entry) {
                $localization[] = $this->normalizer->normalize($entry, $format, $context);
            }
            $data['localization'] = $localization;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof CardData;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [CardData::class => true];
    }
}
