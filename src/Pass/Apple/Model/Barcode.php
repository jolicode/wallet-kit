<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-import-type BarcodeFormat from BarcodeFormatEnum
 *
 * @phpstan-type BarcodeType array{altText: null|string, format: BarcodeFormat, message: string, messageEncoding: string}
 */
class Barcode
{
    public function __construct(public ?string $altText, public BarcodeFormatEnum $format, public string $message, public string $messageEncoding)
    {
    }
}
