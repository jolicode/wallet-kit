<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type LocalizedStringType from LocalizedString
 *
 * @phpstan-type UriType array{uri?: string, description?: string, localizedDescription?: LocalizedStringType, id?: string}
 */
class Uri
{
    public function __construct(
        public ?string $uri = null,
        public ?string $description = null,
        public ?LocalizedString $localizedDescription = null,
        public ?string $id = null,
    ) {
    }
}
