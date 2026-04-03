<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType;

/**
 * @phpstan-type EventDateInfoType array{startDate?: string, endDate?: string, timeZone?: string}
 */
class EventDateInfo
{
    public function __construct(
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?string $timeZone = null,
    ) {
    }
}
