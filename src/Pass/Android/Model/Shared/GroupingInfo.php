<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type GroupingInfoType array{groupingId?: string, sortIndex?: int}
 */
class GroupingInfo
{
    public function __construct(
        public ?string $groupingId = null,
        public ?int $sortIndex = null,
    ) {
    }
}
