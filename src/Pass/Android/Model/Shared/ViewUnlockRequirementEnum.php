<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type ViewUnlockRequirement 'VIEW_UNLOCK_REQUIREMENT_UNSPECIFIED'|'UNLOCK_NOT_REQUIRED'|'UNLOCK_REQUIRED_TO_VIEW'
 */
enum ViewUnlockRequirementEnum: string
{
    case Unspecified = 'VIEW_UNLOCK_REQUIREMENT_UNSPECIFIED';
    case UnlockNotRequired = 'UNLOCK_NOT_REQUIRED';
    case UnlockRequiredToView = 'UNLOCK_REQUIRED_TO_VIEW';
}
