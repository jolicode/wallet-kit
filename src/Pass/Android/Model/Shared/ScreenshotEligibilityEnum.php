<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type ScreenshotEligibility 'SCREENSHOT_ELIGIBILITY_UNSPECIFIED'|'ELIGIBLE'|'INELIGIBLE'
 */
enum ScreenshotEligibilityEnum: string
{
    case Unspecified = 'SCREENSHOT_ELIGIBILITY_UNSPECIFIED';
    case Eligible = 'ELIGIBLE';
    case Ineligible = 'INELIGIBLE';
}
