<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-type RelevantDateType array{startDate: string, endDate: string}
 */
class RelevantDate
{
    public function __construct(
        public string $startDate,
        public string $endDate,
    ) {
    }
}
