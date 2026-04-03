<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type CallbackOptionsType array{url?: string, updateRequestUrl?: string}
 */
class CallbackOptions
{
    public function __construct(
        public ?string $url = null,
        public ?string $updateRequestUrl = null,
    ) {
    }
}
