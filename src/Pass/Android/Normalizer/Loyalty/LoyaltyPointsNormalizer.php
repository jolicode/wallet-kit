<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Loyalty;

use Jolicode\WalletKit\Pass\Android\Model\Loyalty\LoyaltyPoints;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type LoyaltyPointsType from LoyaltyPoints
 */
class LoyaltyPointsNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param LoyaltyPoints        $object
     * @param array<string, mixed> $context
     *
     * @return LoyaltyPointsType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->label) {
            $data['label'] = $object->label;
        }

        if (null !== $object->balance) {
            $data['balance'] = $this->normalizer->normalize($object->balance, $format, $context);
        }

        if (null !== $object->localizedLabel) {
            $data['localizedLabel'] = $this->normalizer->normalize($object->localizedLabel, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof LoyaltyPoints;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [LoyaltyPoints::class => true];
    }
}
