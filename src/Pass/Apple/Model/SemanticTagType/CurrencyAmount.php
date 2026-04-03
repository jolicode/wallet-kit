<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType;

/**
 * @phpstan-type CurrencyAmountType array{amount?: string, currencyCode?: string}
 */
class CurrencyAmount
{
    public function __construct(
        public ?string $amount = null,
        public ?string $currencyCode = null,
    ) {
    }
}
