<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Model\Generic;

use Jolicode\WalletKit\Common\Color;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\Location;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungBarcode;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungImage;

/**
 * @phpstan-import-type SamsungImageType from SamsungImage
 * @phpstan-import-type SamsungBarcodeType from SamsungBarcode
 * @phpstan-import-type SamsungLocationType from Location
 *
 * @phpstan-type GenericAttributesType array{
 *     title: string,
 *     providerName: string,
 *     startDate: int,
 *     noticeDesc: string,
 *     appLinkLogo: string,
 *     appLinkName: string,
 *     appLinkData: string,
 *     mainImg?: string,
 *     subtitle?: string,
 *     eventId?: string,
 *     groupingId?: string,
 *     endDate?: int,
 *     logoImage?: SamsungImageType,
 *     coverImage?: string,
 *     bgImage?: string,
 *     bgColor?: string,
 *     fontColor?: string,
 *     blinkColor?: string,
 *     serial1?: SamsungBarcodeType,
 *     serial2?: SamsungBarcodeType,
 *     csInfo?: string,
 *     providerViewLink?: string,
 *     locations?: list<SamsungLocationType>,
 *     preventCaptureYn?: string,
 *     noNetworkSupportYn?: string,
 *     privacyModeYn?: string,
 * }
 */
class GenericAttributes
{
    /**
     * @param list<Location>|null $locations
     */
    public function __construct(
        public string $title,
        public string $providerName,
        public int $startDate,
        public string $noticeDesc,
        public string $appLinkLogo,
        public string $appLinkName,
        public string $appLinkData,
        public ?string $mainImg = null,
        public ?string $subtitle = null,
        public ?string $eventId = null,
        public ?string $groupingId = null,
        public ?int $endDate = null,
        public ?SamsungImage $logoImage = null,
        public ?string $coverImage = null,
        public ?string $bgImage = null,
        public ?Color $bgColor = null,
        public ?Color $fontColor = null,
        public ?Color $blinkColor = null,
        public ?SamsungBarcode $serial1 = null,
        public ?SamsungBarcode $serial2 = null,
        public ?string $csInfo = null,
        public ?string $providerViewLink = null,
        public ?array $locations = null,
        public ?bool $preventCapture = null,
        public ?bool $noNetworkSupport = null,
        public ?bool $privacyMode = null,
    ) {
    }
}
