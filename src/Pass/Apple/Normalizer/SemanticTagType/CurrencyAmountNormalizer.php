<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType;

use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\CurrencyAmount;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type CurrencyAmountType from CurrencyAmount
 */
class CurrencyAmountNormalizer implements NormalizerInterface
{
    /**
     * @param CurrencyAmount       $object
     * @param array<string, mixed> $context
     *
     * @return CurrencyAmountType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->amount) {
            $data['amount'] = $object->amount;
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
        return $data instanceof CurrencyAmount;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [CurrencyAmount::class => true];
    }
}
