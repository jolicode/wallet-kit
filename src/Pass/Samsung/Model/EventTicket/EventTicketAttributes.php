<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Model\EventTicket;

use Jolicode\WalletKit\Pass\Samsung\Model\Shared\Location;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungBarcode;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungImage;

/**
 * @phpstan-import-type SamsungImageType from SamsungImage
 * @phpstan-import-type SamsungBarcodeType from SamsungBarcode
 * @phpstan-import-type SamsungLocationType from Location
 *
 * @phpstan-type EventTicketAttributesType array{
 *     title: string,
 *     providerName: string,
 *     issueDate: int,
 *     reservationNumber: string,
 *     startDate: int,
 *     noticeDesc: string,
 *     appLinkLogo: string,
 *     appLinkName: string,
 *     appLinkData: string,
 *     mainImg?: string,
 *     logoImage?: SamsungImageType,
 *     endDate?: int,
 *     holderName?: string,
 *     grade?: string,
 *     seatNumber?: string,
 *     entrance?: string,
 *     barcode?: SamsungBarcodeType,
 *     bgColor?: string,
 *     fontColor?: string,
 *     locations?: list<SamsungLocationType>,
 *     preventCaptureYn?: string,
 *     noNetworkSupportYn?: string,
 *     reactivatableYn?: string,
 * }
 */
class EventTicketAttributes
{
    /**
     * @param list<Location>|null $locations
     */
    public function __construct(
        public string $title,
        public string $providerName,
        public int $issueDate,
        public string $reservationNumber,
        public int $startDate,
        public string $noticeDesc,
        public string $appLinkLogo,
        public string $appLinkName,
        public string $appLinkData,
        public ?string $mainImg = null,
        public ?SamsungImage $logoImage = null,
        public ?int $endDate = null,
        public ?string $holderName = null,
        public ?string $grade = null,
        public ?string $seatNumber = null,
        public ?string $entrance = null,
        public ?SamsungBarcode $barcode = null,
        public ?string $bgColor = null,
        public ?string $fontColor = null,
        public ?array $locations = null,
        public ?bool $preventCapture = null,
        public ?bool $noNetworkSupport = null,
        public ?bool $reactivatable = null,
    ) {
    }
}
