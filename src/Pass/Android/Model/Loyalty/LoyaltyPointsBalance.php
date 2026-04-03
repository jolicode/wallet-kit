<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Loyalty;

use Jolicode\WalletKit\Pass\Android\Model\Shared\Money;

/**
 * @phpstan-import-type MoneyType from Money
 *
 * @phpstan-type LoyaltyPointsBalanceType array{string?: string, int?: int, double?: float, money?: MoneyType}
 */
class LoyaltyPointsBalance
{
    public function __construct(
        public ?string $string = null,
        public ?int $int = null,
        public ?float $double = null,
        public ?Money $money = null,
    ) {
    }
}
