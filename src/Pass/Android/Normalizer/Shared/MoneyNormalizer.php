<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\Money;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type MoneyType from Money
 */
class MoneyNormalizer implements NormalizerInterface
{
    /**
     * @param Money                $object
     * @param array<string, mixed> $context
     *
     * @return MoneyType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->micros) {
            $data['micros'] = $object->micros;
        }

        if (null !== $object->currencyCode) {
            $data['currencyCode'] = $object->currencyCode;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Money;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [Money::class => true];
    }
}
