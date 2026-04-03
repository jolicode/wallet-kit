<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type MoneyType array{micros?: string, currencyCode?: string}
 */
class Money
{
    public function __construct(
        public ?string $micros = null,
        public ?string $currencyCode = null,
    ) {
    }
}
