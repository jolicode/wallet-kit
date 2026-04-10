<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Model\Shared;

/**
 * @phpstan-type SerialType 'BARCODE'|'QRCODE'|'SERIAL'|'DUALBARCODE'|'None'
 */
enum SerialTypeEnum: string
{
    case BARCODE = 'BARCODE';
    case QRCODE = 'QRCODE';
    case SERIAL = 'SERIAL';
    case DUAL_BARCODE = 'DUALBARCODE';
    case NONE = 'None';
}
