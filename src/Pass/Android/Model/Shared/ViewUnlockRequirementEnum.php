<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type ViewUnlockRequirement 'VIEW_UNLOCK_REQUIREMENT_UNSPECIFIED'|'UNLOCK_NOT_REQUIRED'|'UNLOCK_REQUIRED_TO_VIEW'
 */
enum ViewUnlockRequirementEnum: string
{
    case UNSPECIFIED = 'VIEW_UNLOCK_REQUIREMENT_UNSPECIFIED';
    case UNLOCK_NOT_REQUIRED = 'UNLOCK_NOT_REQUIRED';
    case UNLOCK_REQUIRED_TO_VIEW = 'UNLOCK_REQUIRED_TO_VIEW';
}
