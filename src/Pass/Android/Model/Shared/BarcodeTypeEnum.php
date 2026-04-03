<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type BarcodeType 'AZTEC'|'CODE_39'|'CODE_128'|'CODABAR'|'DATA_MATRIX'|'EAN_8'|'EAN_13'|'ITF_14'|'PDF_417'|'QR_CODE'|'UPC_A'|'TEXT_ONLY'
 */
enum BarcodeTypeEnum: string
{
    case Aztec = 'AZTEC';
    case Code39 = 'CODE_39';
    case Code128 = 'CODE_128';
    case Codabar = 'CODABAR';
    case DataMatrix = 'DATA_MATRIX';
    case Ean8 = 'EAN_8';
    case Ean13 = 'EAN_13';
    case Itf14 = 'ITF_14';
    case Pdf417 = 'PDF_417';
    case QrCode = 'QR_CODE';
    case UpcA = 'UPC_A';
    case TextOnly = 'TEXT_ONLY';
}
