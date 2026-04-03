<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Flight;

use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;

/**
 * @phpstan-import-type LocalizedStringType from LocalizedString
 *
 * @phpstan-type AirportInfoType array{airportIataCode?: string, airportNameOverride?: LocalizedStringType, terminal?: string, gate?: string}
 */
class AirportInfo
{
    public function __construct(
        public ?string $airportIataCode = null,
        public ?LocalizedString $airportNameOverride = null,
        public ?string $terminal = null,
        public ?string $gate = null,
    ) {
    }
}
