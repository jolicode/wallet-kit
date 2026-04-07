<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type SaveRestrictionStatus from SaveRestrictionStatusEnum
 *
 * @phpstan-type SaveRestrictionsType array{restrictionStatus?: SaveRestrictionStatus}
 */
class SaveRestrictions
{
    public function __construct(
        public ?SaveRestrictionStatusEnum $restrictionStatus = null,
    ) {
    }
}
