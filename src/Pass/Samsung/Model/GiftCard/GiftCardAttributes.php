<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Model\GiftCard;

use Jolicode\WalletKit\Pass\Samsung\Model\Shared\Location;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungBarcode;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungImage;

/**
 * @phpstan-import-type SamsungImageType from SamsungImage
 * @phpstan-import-type SamsungBarcodeType from SamsungBarcode
 * @phpstan-import-type SamsungLocationType from Location
 *
 * @phpstan-type GiftCardAttributesType array{
 *     title: string,
 *     providerName: string,
 *     appLinkLogo: string,
 *     appLinkName: string,
 *     appLinkData: string,
 *     logoImage?: SamsungImageType,
 *     user?: string,
 *     startDate?: int,
 *     endDate?: int,
 *     barcode?: SamsungBarcodeType,
 *     bgColor?: string,
 *     fontColor?: string,
 *     bgImage?: string,
 *     mainImg?: string,
 *     blinkColor?: string,
 *     noticeDesc?: string,
 *     csInfo?: string,
 *     merchantId?: string,
 *     merchantName?: string,
 *     amount?: string,
 *     balance?: string,
 *     summaryUrl?: string,
 *     locations?: list<SamsungLocationType>,
 *     preventCaptureYn?: string,
 * }
 */
class GiftCardAttributes
{
    /**
     * @param list<Location>|null $locations
     */
    public function __construct(
        public string $title,
        public string $providerName,
        public string $appLinkLogo,
        public string $appLinkName,
        public string $appLinkData,
        public ?SamsungImage $logoImage = null,
        public ?string $user = null,
        public ?int $startDate = null,
        public ?int $endDate = null,
        public ?SamsungBarcode $barcode = null,
        public ?string $bgColor = null,
        public ?string $fontColor = null,
        public ?string $bgImage = null,
        public ?string $mainImg = null,
        public ?string $blinkColor = null,
        public ?string $noticeDesc = null,
        public ?string $csInfo = null,
        public ?string $merchantId = null,
        public ?string $merchantName = null,
        public ?string $amount = null,
        public ?string $balance = null,
        public ?string $summaryUrl = null,
        public ?array $locations = null,
        public ?bool $preventCapture = null,
    ) {
    }
}
