<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungImage;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type SamsungImageType from SamsungImage
 */
class SamsungImageNormalizer implements NormalizerInterface
{
    /**
     * @param SamsungImage         $object
     * @param array<string, mixed> $context
     *
     * @return SamsungImageType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->darkUrl) {
            $data['darkUrl'] = $object->darkUrl;
        }

        if (null !== $object->lightUrl) {
            $data['lightUrl'] = $object->lightUrl;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof SamsungImage;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [SamsungImage::class => true];
    }
}
