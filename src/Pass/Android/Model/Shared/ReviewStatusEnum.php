<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type ReviewStatus 'DRAFT'|'UNDER_REVIEW'|'APPROVED'
 */
enum ReviewStatusEnum: string
{
    case Draft = 'DRAFT';
    case UnderReview = 'UNDER_REVIEW';
    case Approved = 'APPROVED';
}
