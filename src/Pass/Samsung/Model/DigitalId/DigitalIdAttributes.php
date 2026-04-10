<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Model\DigitalId;

use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungBarcode;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungImage;

/**
 * @phpstan-import-type SamsungImageType from SamsungImage
 * @phpstan-import-type SamsungBarcodeType from SamsungBarcode
 *
 * @phpstan-type DigitalIdAttributesType array{
 *     title: string,
 *     holderName: string,
 *     identifier: string,
 *     issueDate: int,
 *     providerName: string,
 *     csInfo: string,
 *     logoImage?: SamsungImageType,
 *     secondHolderName?: string,
 *     organization?: string,
 *     position?: string,
 *     idNumber?: string,
 *     address?: string,
 *     birthdate?: string,
 *     gender?: string,
 *     classification?: string,
 *     expiry?: int,
 *     issuerName?: string,
 *     extraInfo?: string,
 *     noticeDesc?: string,
 *     barcode?: SamsungBarcodeType,
 *     bgColor?: string,
 *     fontColor?: string,
 *     bgImage?: string,
 *     coverImage?: string,
 *     blinkColor?: string,
 *     appLinkLogo?: string,
 *     appLinkName?: string,
 *     appLinkData?: string,
 *     preventCaptureYn?: string,
 *     privacyModeYn?: string,
 * }
 */
class DigitalIdAttributes
{
    public function __construct(
        public string $title,
        public string $holderName,
        public string $identifier,
        public int $issueDate,
        public string $providerName,
        public string $csInfo,
        public ?SamsungImage $logoImage = null,
        public ?string $secondHolderName = null,
        public ?string $organization = null,
        public ?string $position = null,
        public ?string $idNumber = null,
        public ?string $address = null,
        public ?string $birthdate = null,
        public ?string $gender = null,
        public ?string $classification = null,
        public ?int $expiry = null,
        public ?string $issuerName = null,
        public ?string $extraInfo = null,
        public ?string $noticeDesc = null,
        public ?SamsungBarcode $barcode = null,
        public ?string $bgColor = null,
        public ?string $fontColor = null,
        public ?string $bgImage = null,
        public ?string $coverImage = null,
        public ?string $blinkColor = null,
        public ?string $appLinkLogo = null,
        public ?string $appLinkName = null,
        public ?string $appLinkData = null,
        public ?bool $preventCapture = null,
        public ?bool $privacyMode = null,
    ) {
    }
}
