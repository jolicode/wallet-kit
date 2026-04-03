<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder;

use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;

/**
 * Shared identifiers and defaults for dual-platform wallet payloads.
 */
final class WalletPlatformContext
{
    public function __construct(
        public readonly string $appleTeamIdentifier,
        public readonly string $applePassTypeIdentifier,
        public readonly string $appleSerialNumber,
        public readonly string $appleOrganizationName,
        public readonly string $appleDescription,
        public readonly string $googleClassId,
        public readonly string $googleObjectId,
        public readonly int $appleFormatVersion = 1,
        public readonly ReviewStatusEnum $defaultGoogleReviewStatus = ReviewStatusEnum::Draft,
        public readonly StateEnum $defaultGoogleObjectState = StateEnum::Active,
    ) {
    }
}
