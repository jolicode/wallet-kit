<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Model\Coupon;

use Jolicode\WalletKit\Common\Color;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungBarcode;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungImage;

/**
 * @phpstan-import-type SamsungImageType from SamsungImage
 * @phpstan-import-type SamsungBarcodeType from SamsungBarcode
 *
 * @phpstan-type CouponAttributesType array{
 *     title: string,
 *     appLinkLogo: string,
 *     appLinkName: string,
 *     appLinkData: string,
 *     issueDate: int,
 *     expiry: int,
 *     mainImg?: string,
 *     logoImage?: SamsungImageType,
 *     brandName?: string,
 *     noticeDesc?: string,
 *     barcode?: SamsungBarcodeType,
 *     bgColor?: string,
 *     fontColor?: string,
 *     balance?: string,
 *     summaryUrl?: string,
 *     editableYn?: string,
 *     deletableYn?: string,
 *     displayRedeemButtonYn?: string,
 *     notificationYn?: string,
 *     preventCaptureYn?: string,
 * }
 */
class CouponAttributes
{
    public function __construct(
        public string $title,
        public string $appLinkLogo,
        public string $appLinkName,
        public string $appLinkData,
        public int $issueDate,
        public int $expiry,
        public ?string $mainImg = null,
        public ?SamsungImage $logoImage = null,
        public ?string $brandName = null,
        public ?string $noticeDesc = null,
        public ?SamsungBarcode $barcode = null,
        public ?Color $bgColor = null,
        public ?Color $fontColor = null,
        public ?string $balance = null,
        public ?string $summaryUrl = null,
        public ?bool $editable = null,
        public ?bool $deletable = null,
        public ?bool $displayRedeemButton = null,
        public ?bool $notification = null,
        public ?bool $preventCapture = null,
    ) {
    }
}
