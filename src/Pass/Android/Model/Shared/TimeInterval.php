<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type GoogleDateTimeType from GoogleDateTime
 *
 * @phpstan-type TimeIntervalType array{start?: GoogleDateTimeType, end?: GoogleDateTimeType}
 */
class TimeInterval
{
    public function __construct(
        public ?GoogleDateTime $start = null,
        public ?GoogleDateTime $end = null,
    ) {
    }
}
