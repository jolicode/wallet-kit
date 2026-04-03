<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type UriType from Uri
 *
 * @phpstan-type AppTargetType array{targetUri?: UriType, packageName?: string}
 */
class AppTarget
{
    public function __construct(
        public ?Uri $targetUri = null,
        public ?string $packageName = null,
    ) {
    }
}
