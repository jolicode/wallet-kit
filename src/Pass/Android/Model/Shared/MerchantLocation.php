<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type LocalizedStringType from LocalizedString
 *
 * @phpstan-type MerchantLocationType array{merchantName?: LocalizedStringType, latitude?: float, longitude?: float}
 */
class MerchantLocation
{
    public function __construct(
        public ?LocalizedString $merchantName = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
    ) {
    }
}
