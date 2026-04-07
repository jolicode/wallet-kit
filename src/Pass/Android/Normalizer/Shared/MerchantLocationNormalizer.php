<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\MerchantLocation;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type MerchantLocationType from MerchantLocation
 */
class MerchantLocationNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param MerchantLocation     $object
     * @param array<string, mixed> $context
     *
     * @return MerchantLocationType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->merchantName) {
            $data['merchantName'] = $this->normalizer->normalize($object->merchantName, $format, $context);
        }

        if (null !== $object->latitude) {
            $data['latitude'] = $object->latitude;
        }

        if (null !== $object->longitude) {
            $data['longitude'] = $object->longitude;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof MerchantLocation;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [MerchantLocation::class => true];
    }
}
