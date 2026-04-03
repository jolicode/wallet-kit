<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type GoogleDateTimeType array{date?: string}
 */
class GoogleDateTime
{
    public function __construct(
        public ?string $date = null,
    ) {
    }
}
