<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type SaveRestrictionStatus 'RESTRICTION_STATUS_UNSPECIFIED'|'UNRESTRICTED'|'FULLY_RESTRICTED'
 */
enum SaveRestrictionStatusEnum: string
{
    case RESTRICTION_STATUS_UNSPECIFIED = 'RESTRICTION_STATUS_UNSPECIFIED';
    case UNRESTRICTED = 'UNRESTRICTED';
    case FULLY_RESTRICTED = 'FULLY_RESTRICTED';
}
