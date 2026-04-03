<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\TranslatedString;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type TranslatedStringType from TranslatedString
 */
class TranslatedStringNormalizer implements NormalizerInterface
{
    /**
     * @param TranslatedString     $object
     * @param array<string, mixed> $context
     *
     * @return TranslatedStringType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->language) {
            $data['language'] = $object->language;
        }

        if (null !== $object->value) {
            $data['value'] = $object->value;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof TranslatedString;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [TranslatedString::class => true];
    }
}
