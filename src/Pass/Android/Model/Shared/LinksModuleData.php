<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type UriType from Uri
 *
 * @phpstan-type LinksModuleDataType array{uris?: list<UriType>}
 */
class LinksModuleData
{
    /**
     * @param list<Uri>|null $uris
     */
    public function __construct(
        public ?array $uris = null,
    ) {
    }
}
