<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type ScreenshotEligibility from ScreenshotEligibilityEnum
 * @phpstan-import-type NfcConstraint from NfcConstraintEnum
 *
 * @phpstan-type PassConstraintsType array{screenshotEligibility?: ScreenshotEligibility, nfcConstraint?: list<NfcConstraint>}
 */
class PassConstraints
{
    /**
     * @param list<NfcConstraintEnum>|null $nfcConstraint
     */
    public function __construct(
        public ?ScreenshotEligibilityEnum $screenshotEligibility = null,
        public ?array $nfcConstraint = null,
    ) {
    }
}
