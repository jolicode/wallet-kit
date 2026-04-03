<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type BarcodeType from BarcodeTypeEnum
 * @phpstan-import-type BarcodeRenderEncoding from BarcodeRenderEncodingEnum
 * @phpstan-import-type LocalizedStringType from LocalizedString
 *
 * @phpstan-type GoogleBarcodeType array{type?: BarcodeType, value?: string, alternateText?: string, renderEncoding?: BarcodeRenderEncoding, showCodeText?: LocalizedStringType}
 */
class Barcode
{
    public function __construct(
        public ?BarcodeTypeEnum $type = null,
        public ?string $value = null,
        public ?string $alternateText = null,
        public ?BarcodeRenderEncodingEnum $renderEncoding = null,
        public ?LocalizedString $showCodeText = null,
    ) {
    }
}
