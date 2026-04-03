<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type ReviewStatus 'DRAFT'|'UNDER_REVIEW'|'APPROVED'
 */
enum ReviewStatusEnum: string
{
    case DRAFT = 'DRAFT';
    case UNDER_REVIEW = 'UNDER_REVIEW';
    case APPROVED = 'APPROVED';
}
