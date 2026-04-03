<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;

/**
 * @phpstan-import-type LocalizedStringType from LocalizedString
 *
 * @phpstan-type TicketRestrictionsType array{routeRestrictions?: LocalizedStringType, routeRestrictionsDetails?: LocalizedStringType, timeRestrictions?: LocalizedStringType, otherRestrictions?: LocalizedStringType}
 */
class TicketRestrictions
{
    public function __construct(
        public ?LocalizedString $routeRestrictions = null,
        public ?LocalizedString $routeRestrictionsDetails = null,
        public ?LocalizedString $timeRestrictions = null,
        public ?LocalizedString $otherRestrictions = null,
    ) {
    }
}
