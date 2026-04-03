<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

/**
 * @phpstan-type ConcessionCategory 'CONCESSION_CATEGORY_UNSPECIFIED'|'ADULT'|'CHILD'|'SENIOR'
 */
enum ConcessionCategoryEnum: string
{
    case Unspecified = 'CONCESSION_CATEGORY_UNSPECIFIED';
    case Adult = 'ADULT';
    case Child = 'CHILD';
    case Senior = 'SENIOR';
}
