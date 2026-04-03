<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-type BarcodeFormat 'PKBarcodeFormatQR'|'PKBarcodeFormatPDF417'|'PKBarcodeFormatAztec'|'PKBarcodeFormatCode128'
 */
enum BarcodeFormatEnum: string
{
    case QR = 'PKBarcodeFormatQR';
    case PDF417 = 'PKBarcodeFormatPDF417';
    case AZTEC = 'PKBarcodeFormatAztec';
    case CODE128 = 'PKBarcodeFormatCode128';
}
