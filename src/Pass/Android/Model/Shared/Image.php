<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type ImageUriType from ImageUri
 * @phpstan-import-type LocalizedStringType from LocalizedString
 *
 * @phpstan-type ImageType array{sourceUri?: ImageUriType, contentDescription?: LocalizedStringType}
 */
class Image
{
    public function __construct(
        public ?ImageUri $sourceUri = null,
        public ?LocalizedString $contentDescription = null,
    ) {
    }
}
