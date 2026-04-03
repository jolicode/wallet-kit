<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Flight;

use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;

/**
 * @phpstan-import-type LocalizedStringType from LocalizedString
 *
 * @phpstan-type FrequentFlyerInfoType array{frequentFlyerProgramName?: LocalizedStringType, frequentFlyerNumber?: string}
 */
class FrequentFlyerInfo
{
    public function __construct(
        public ?LocalizedString $frequentFlyerProgramName = null,
        public ?string $frequentFlyerNumber = null,
    ) {
    }
}
