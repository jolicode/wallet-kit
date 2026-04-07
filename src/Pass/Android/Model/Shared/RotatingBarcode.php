<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type BarcodeType from BarcodeTypeEnum
 * @phpstan-import-type BarcodeRenderEncoding from BarcodeRenderEncodingEnum
 * @phpstan-import-type LocalizedStringType from LocalizedString
 * @phpstan-import-type RotatingBarcodeTotpDetailsType from RotatingBarcodeTotpDetails
 * @phpstan-import-type RotatingBarcodeValuesType from RotatingBarcodeValues
 *
 * @phpstan-type RotatingBarcodeType array{type?: BarcodeType, renderEncoding?: BarcodeRenderEncoding, valuePattern?: string, alternateText?: string, showCodeText?: LocalizedStringType, totpDetails?: RotatingBarcodeTotpDetailsType, values?: RotatingBarcodeValuesType}
 */
class RotatingBarcode
{
    public function __construct(
        public ?BarcodeTypeEnum $type = null,
        public ?BarcodeRenderEncodingEnum $renderEncoding = null,
        public ?string $valuePattern = null,
        public ?string $alternateText = null,
        public ?LocalizedString $showCodeText = null,
        public ?RotatingBarcodeTotpDetails $totpDetails = null,
        public ?RotatingBarcodeValues $values = null,
    ) {
    }
}
