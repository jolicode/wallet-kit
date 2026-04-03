<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type ScreenshotEligibility 'SCREENSHOT_ELIGIBILITY_UNSPECIFIED'|'ELIGIBLE'|'INELIGIBLE'
 */
enum ScreenshotEligibilityEnum: string
{
    case UNSPECIFIED = 'SCREENSHOT_ELIGIBILITY_UNSPECIFIED';
    case ELIGIBLE = 'ELIGIBLE';
    case INELIGIBLE = 'INELIGIBLE';
}
