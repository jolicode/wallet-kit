<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder;

use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;

/**
 * Google Wallet class/object identifiers, defaults, and issuer display name for class payloads.
 */
final class GoogleWalletContext
{
    public function __construct(
        public readonly string $classId,
        public readonly string $objectId,
        public readonly ReviewStatusEnum $defaultReviewStatus = ReviewStatusEnum::DRAFT,
        public readonly StateEnum $defaultObjectState = StateEnum::ACTIVE,
        public readonly ?string $issuerName = null,
    ) {
    }
}
