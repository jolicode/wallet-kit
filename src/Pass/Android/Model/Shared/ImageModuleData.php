<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type ImageType from Image
 *
 * @phpstan-type ImageModuleDataType array{mainImage?: ImageType, id?: string}
 */
class ImageModuleData
{
    public function __construct(
        public ?Image $mainImage = null,
        public ?string $id = null,
    ) {
    }
}
