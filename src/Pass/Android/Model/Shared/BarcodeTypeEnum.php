<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type BarcodeType 'BARCODE_TYPE_UNSPECIFIED'|'AZTEC'|'CODE_39'|'CODE_128'|'CODABAR'|'DATA_MATRIX'|'EAN_8'|'EAN_13'|'EAN13'|'ITF_14'|'PDF_417'|'PDF417'|'QR_CODE'|'UPC_A'|'TEXT_ONLY'
 */
enum BarcodeTypeEnum: string
{
    case BARCODE_TYPE_UNSPECIFIED = 'BARCODE_TYPE_UNSPECIFIED';
    case AZTEC = 'AZTEC';
    case CODE_39 = 'CODE_39';
    case CODE_128 = 'CODE_128';
    case CODABAR = 'CODABAR';
    case DATA_MATRIX = 'DATA_MATRIX';
    case EAN_8 = 'EAN_8';
    case EAN_13 = 'EAN_13';
    case ITF_14 = 'ITF_14';
    case PDF_417 = 'PDF_417';
    case QR_CODE = 'QR_CODE';
    case UPC_A = 'UPC_A';
    case TEXT_ONLY = 'TEXT_ONLY';
    case EAN13 = 'EAN13';
    case PDF417 = 'PDF417';
}
