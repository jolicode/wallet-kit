<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder\Internal;

use Jolicode\WalletKit\Pass\Apple\Model\Barcode as AppleBarcode;
use Jolicode\WalletKit\Pass\Apple\Model\BarcodeFormatEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungBarcode;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SerialTypeEnum;

final class SamsungBarcodeMapper
{
    /**
     * @param list<AppleBarcode> $appleBarcodes
     */
    public static function fromFirstAppleBarcode(array $appleBarcodes): ?SamsungBarcode
    {
        if ([] === $appleBarcodes) {
            return null;
        }

        return self::fromAppleBarcode($appleBarcodes[0]);
    }

    public static function fromAppleBarcode(AppleBarcode $barcode): SamsungBarcode
    {
        $serialType = match ($barcode->format) {
            BarcodeFormatEnum::QR => SerialTypeEnum::QRCODE,
            BarcodeFormatEnum::PDF417, BarcodeFormatEnum::AZTEC, BarcodeFormatEnum::CODE_128 => SerialTypeEnum::BARCODE,
        };

        return new SamsungBarcode(
            serialType: $serialType,
            value: $barcode->message,
        );
    }
}
