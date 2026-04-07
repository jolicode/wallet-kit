<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type LocalizedStringType from LocalizedString
 * @phpstan-import-type UriType from Uri
 * @phpstan-import-type ImageType from Image
 *
 * @phpstan-type ValueAddedFieldType array{header?: string, localizedHeader?: LocalizedStringType, body?: string, localizedBody?: LocalizedStringType, actionUri?: UriType, image?: ImageType}
 */
class ValueAddedField
{
    public function __construct(
        public ?string $header = null,
        public ?LocalizedString $localizedHeader = null,
        public ?string $body = null,
        public ?LocalizedString $localizedBody = null,
        public ?Uri $actionUri = null,
        public ?Image $image = null,
    ) {
    }
}
