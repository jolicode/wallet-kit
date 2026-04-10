<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Model\Shared;

/**
 * @phpstan-type SamsungImageType array{darkUrl?: string, lightUrl?: string}
 */
class SamsungImage
{
    public function __construct(
        public ?string $darkUrl = null,
        public ?string $lightUrl = null,
    ) {
    }
}
