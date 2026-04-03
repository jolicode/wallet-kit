<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type LocalizedStringType from LocalizedString
 */
class LocalizedStringNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param LocalizedString      $object
     * @param array<string, mixed> $context
     *
     * @return LocalizedStringType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->defaultValue) {
            $data['defaultValue'] = $this->normalizer->normalize($object->defaultValue, $format, $context);
        }

        if (null !== $object->translatedValues) {
            $normalized = [];
            foreach ($object->translatedValues as $translatedValue) {
                $normalized[] = $this->normalizer->normalize($translatedValue, $format, $context);
            }
            $data['translatedValues'] = $normalized;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof LocalizedString;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [LocalizedString::class => true];
    }
}
