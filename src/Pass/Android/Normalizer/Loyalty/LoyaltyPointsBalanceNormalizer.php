<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Loyalty;

use Jolicode\WalletKit\Pass\Android\Model\Loyalty\LoyaltyPointsBalance;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type LoyaltyPointsBalanceType from LoyaltyPointsBalance
 */
class LoyaltyPointsBalanceNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param LoyaltyPointsBalance $object
     * @param array<string, mixed> $context
     *
     * @return LoyaltyPointsBalanceType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->string) {
            $data['string'] = $object->string;
        }

        if (null !== $object->int) {
            $data['int'] = $object->int;
        }

        if (null !== $object->double) {
            $data['double'] = $object->double;
        }

        if (null !== $object->money) {
            $data['money'] = $this->normalizer->normalize($object->money, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof LoyaltyPointsBalance;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [LoyaltyPointsBalance::class => true];
    }
}
