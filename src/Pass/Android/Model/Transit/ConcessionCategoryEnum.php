<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

/**
 * @phpstan-type ConcessionCategory 'CONCESSION_CATEGORY_UNSPECIFIED'|'ADULT'|'CHILD'|'SENIOR'
 */
enum ConcessionCategoryEnum: string
{
    case UNSPECIFIED = 'CONCESSION_CATEGORY_UNSPECIFIED';
    case ADULT = 'ADULT';
    case CHILD = 'CHILD';
    case SENIOR = 'SENIOR';
}
