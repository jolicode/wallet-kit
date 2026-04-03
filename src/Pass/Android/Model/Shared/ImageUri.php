<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type ImageUriType array{uri?: string}
 */
class ImageUri
{
    public function __construct(
        public ?string $uri = null,
    ) {
    }
}
