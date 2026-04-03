<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Loyalty;

use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;

/**
 * @phpstan-import-type LoyaltyPointsBalanceType from LoyaltyPointsBalance
 * @phpstan-import-type LocalizedStringType from LocalizedString
 *
 * @phpstan-type LoyaltyPointsType array{label?: string, balance?: LoyaltyPointsBalanceType, localizedLabel?: LocalizedStringType}
 */
class LoyaltyPoints
{
    public function __construct(
        public ?string $label = null,
        public ?LoyaltyPointsBalance $balance = null,
        public ?LocalizedString $localizedLabel = null,
    ) {
    }
}
