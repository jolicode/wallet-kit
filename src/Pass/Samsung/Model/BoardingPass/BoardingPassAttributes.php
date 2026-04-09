<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Model\BoardingPass;

use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungBarcode;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungImage;

/**
 * @phpstan-import-type SamsungImageType from SamsungImage
 * @phpstan-import-type SamsungBarcodeType from SamsungBarcode
 *
 * @phpstan-type BoardingPassAttributesType array{
 *     title: string,
 *     providerName: string,
 *     bgColor: string,
 *     appLinkLogo: string,
 *     appLinkName: string,
 *     appLinkData: string,
 *     providerLogo?: SamsungImageType,
 *     user?: string,
 *     vehicleNumber?: string,
 *     seatClass?: string,
 *     seatNumber?: string,
 *     reservationNumber?: string,
 *     departName?: string,
 *     departCode?: string,
 *     departTerminal?: string,
 *     arriveName?: string,
 *     arriveCode?: string,
 *     arriveTerminal?: string,
 *     estimatedOrActualStartDate?: int,
 *     estimatedOrActualEndDate?: int,
 *     boardingTime?: int,
 *     gateClosingTime?: int,
 *     gate?: string,
 *     boardingGroup?: string,
 *     boardingSeqNo?: string,
 *     baggageAllowance?: string,
 *     barcode?: SamsungBarcodeType,
 *     noticeDesc?: string,
 *     preventCaptureYn?: string,
 * }
 */
class BoardingPassAttributes
{
    public function __construct(
        public string $title,
        public string $providerName,
        public string $bgColor,
        public string $appLinkLogo,
        public string $appLinkName,
        public string $appLinkData,
        public ?SamsungImage $providerLogo = null,
        public ?string $user = null,
        public ?string $vehicleNumber = null,
        public ?string $seatClass = null,
        public ?string $seatNumber = null,
        public ?string $reservationNumber = null,
        public ?string $departName = null,
        public ?string $departCode = null,
        public ?string $departTerminal = null,
        public ?string $arriveName = null,
        public ?string $arriveCode = null,
        public ?string $arriveTerminal = null,
        public ?int $estimatedOrActualStartDate = null,
        public ?int $estimatedOrActualEndDate = null,
        public ?int $boardingTime = null,
        public ?int $gateClosingTime = null,
        public ?string $gate = null,
        public ?string $boardingGroup = null,
        public ?string $boardingSeqNo = null,
        public ?string $baggageAllowance = null,
        public ?SamsungBarcode $barcode = null,
        public ?string $noticeDesc = null,
        public ?bool $preventCapture = null,
    ) {
    }
}
