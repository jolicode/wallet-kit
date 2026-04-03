<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Money;

/**
 * @phpstan-import-type MoneyType from Money
 * @phpstan-import-type LocalizedStringType from LocalizedString
 *
 * @phpstan-type TicketCostType array{faceValue?: MoneyType, purchasePrice?: MoneyType, discountMessage?: LocalizedStringType}
 */
class TicketCost
{
    public function __construct(
        public ?Money $faceValue = null,
        public ?Money $purchasePrice = null,
        public ?LocalizedString $discountMessage = null,
    ) {
    }
}
