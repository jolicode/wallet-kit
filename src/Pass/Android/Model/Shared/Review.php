<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type ReviewType array{comments?: string}
 */
class Review
{
    public function __construct(
        public ?string $comments = null,
    ) {
    }
}
