<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type ReviewStatus 'REVIEW_STATUS_UNSPECIFIED'|'DRAFT'|'UNDER_REVIEW'|'APPROVED'|'REJECTED'
 */
enum ReviewStatusEnum: string
{
    case UNSPECIFIED = 'REVIEW_STATUS_UNSPECIFIED';
    case DRAFT = 'DRAFT';
    case UNDER_REVIEW = 'UNDER_REVIEW';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
}
