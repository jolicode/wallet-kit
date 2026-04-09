<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Model\PayAsYouGo;

use Jolicode\WalletKit\Pass\Samsung\Model\Shared\Location;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungBarcode;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungImage;

/**
 * @phpstan-import-type SamsungImageType from SamsungImage
 * @phpstan-import-type SamsungBarcodeType from SamsungBarcode
 * @phpstan-import-type SamsungLocationType from Location
 *
 * @phpstan-type PayAsYouGoAttributesType array{
 *     title: string,
 *     noticeDesc: string,
 *     appLinkLogo: string,
 *     appLinkName: string,
 *     appLinkData: string,
 *     barcode: SamsungBarcodeType,
 *     subtitle1?: string,
 *     logoImage?: SamsungImageType,
 *     providerName?: string,
 *     holderName?: string,
 *     startDate?: int,
 *     endDate?: int,
 *     bgColor?: string,
 *     fontColor?: string,
 *     bgImage?: string,
 *     blinkColor?: string,
 *     csInfo?: string,
 *     identifier?: string,
 *     grade?: string,
 *     summaryUrl?: string,
 *     locations?: list<SamsungLocationType>,
 *     preventCaptureYn?: string,
 * }
 */
class PayAsYouGoAttributes
{
    /**
     * @param list<Location>|null $locations
     */
    public function __construct(
        public string $title,
        public string $noticeDesc,
        public string $appLinkLogo,
        public string $appLinkName,
        public string $appLinkData,
        public SamsungBarcode $barcode,
        public ?string $subtitle1 = null,
        public ?SamsungImage $logoImage = null,
        public ?string $providerName = null,
        public ?string $holderName = null,
        public ?int $startDate = null,
        public ?int $endDate = null,
        public ?string $bgColor = null,
        public ?string $fontColor = null,
        public ?string $bgImage = null,
        public ?string $blinkColor = null,
        public ?string $csInfo = null,
        public ?string $identifier = null,
        public ?string $grade = null,
        public ?string $summaryUrl = null,
        public ?array $locations = null,
        public ?bool $preventCapture = null,
    ) {
    }
}
