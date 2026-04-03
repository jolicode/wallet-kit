<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type LocalizedStringType from LocalizedString
 *
 * @phpstan-type TextModuleDataType array{header?: string, body?: string, id?: string, localizedHeader?: LocalizedStringType, localizedBody?: LocalizedStringType}
 */
class TextModuleData
{
    public function __construct(
        public ?string $header = null,
        public ?string $body = null,
        public ?string $id = null,
        public ?LocalizedString $localizedHeader = null,
        public ?LocalizedString $localizedBody = null,
    ) {
    }
}
