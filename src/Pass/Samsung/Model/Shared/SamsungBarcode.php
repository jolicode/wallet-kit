<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Model\Shared;

/**
 * @phpstan-import-type SerialType from SerialTypeEnum
 *
 * @phpstan-type SamsungBarcodeType array{serialType: SerialType, value?: string, ptFormat?: string, ptSubFormat?: string, pin?: string}
 */
class SamsungBarcode
{
    public function __construct(
        public SerialTypeEnum $serialType,
        public ?string $value = null,
        public ?string $ptFormat = null,
        public ?string $ptSubFormat = null,
        public ?string $pin = null,
    ) {
    }
}
