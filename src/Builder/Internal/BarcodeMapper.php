<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder\Internal;

use Jolicode\WalletKit\Pass\Android\Model\Shared\Barcode as GoogleBarcode;
use Jolicode\WalletKit\Pass\Android\Model\Shared\BarcodeTypeEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Barcode as AppleBarcode;
use Jolicode\WalletKit\Pass\Apple\Model\BarcodeFormatEnum;

final class BarcodeMapper
{
    /**
     * Uses the first Apple barcode when building the Google object barcode.
     * Google Wallet objects typically expose a single barcode.
     */
    public static function fromFirstAppleBarcode(array $appleBarcodes): ?GoogleBarcode
    {
        if ([] === $appleBarcodes) {
            return null;
        }

        $first = $appleBarcodes[0];
        if (!$first instanceof AppleBarcode) {
            return null;
        }

        return self::fromAppleBarcode($first);
    }

    public static function fromAppleBarcode(AppleBarcode $barcode): GoogleBarcode
    {
        $type = match ($barcode->format) {
            BarcodeFormatEnum::QR => BarcodeTypeEnum::QR_CODE,
            BarcodeFormatEnum::PDF417 => BarcodeTypeEnum::PDF_417,
            BarcodeFormatEnum::AZTEC => BarcodeTypeEnum::AZTEC,
            BarcodeFormatEnum::CODE_128 => BarcodeTypeEnum::CODE_128,
        };

        return new GoogleBarcode(
            type: $type,
            value: $barcode->message,
            alternateText: $barcode->altText,
        );
    }
}
