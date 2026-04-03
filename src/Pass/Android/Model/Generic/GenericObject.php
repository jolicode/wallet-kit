<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Generic;

use Jolicode\WalletKit\Pass\Android\Model\Shared\AppLinkData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Barcode;
use Jolicode\WalletKit\Pass\Android\Model\Shared\GroupingInfo;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Image;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ImageModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LinksModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Message;
use Jolicode\WalletKit\Pass\Android\Model\Shared\PassConstraints;
use Jolicode\WalletKit\Pass\Android\Model\Shared\RotatingBarcode;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TextModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TimeInterval;

/**
 * @phpstan-import-type GenericType from GenericTypeEnum
 * @phpstan-import-type LocalizedStringType from LocalizedString
 * @phpstan-import-type ImageType from Image
 * @phpstan-import-type NotificationsType from Notifications
 * @phpstan-import-type GoogleBarcodeType from Barcode
 * @phpstan-import-type TimeIntervalType from TimeInterval
 * @phpstan-import-type ImageModuleDataType from ImageModuleData
 * @phpstan-import-type TextModuleDataType from TextModuleData
 * @phpstan-import-type LinksModuleDataType from LinksModuleData
 * @phpstan-import-type AppLinkDataType from AppLinkData
 * @phpstan-import-type GroupingInfoType from GroupingInfo
 * @phpstan-import-type RotatingBarcodeType from RotatingBarcode
 * @phpstan-import-type State from StateEnum
 * @phpstan-import-type GoogleMessageType from Message
 * @phpstan-import-type PassConstraintsType from PassConstraints
 *
 * @phpstan-type GenericObjectType array{id: string, classId: string, genericType?: GenericType, cardTitle?: LocalizedStringType, subheader?: LocalizedStringType, header?: LocalizedStringType, logo?: ImageType, wideLogo?: ImageType, hexBackgroundColor?: string, notifications?: NotificationsType, barcode?: GoogleBarcodeType, heroImage?: ImageType, validTimeInterval?: TimeIntervalType, imageModulesData?: list<ImageModuleDataType>, textModulesData?: list<TextModuleDataType>, linksModuleData?: LinksModuleDataType, appLinkData?: AppLinkDataType, groupingInfo?: GroupingInfoType, smartTapRedemptionValue?: string, rotatingBarcode?: RotatingBarcodeType, state?: State, messages?: list<GoogleMessageType>, passConstraints?: PassConstraintsType, linkedObjectIds?: list<string>}
 */
class GenericObject
{
    /**
     * @param list<ImageModuleData>|null $imageModulesData
     * @param list<TextModuleData>|null  $textModulesData
     * @param list<Message>|null         $messages
     * @param list<string>|null          $linkedObjectIds
     */
    public function __construct(
        public string $id,
        public string $classId,
        public ?GenericTypeEnum $genericType = null,
        public ?LocalizedString $cardTitle = null,
        public ?LocalizedString $subheader = null,
        public ?LocalizedString $header = null,
        public ?Image $logo = null,
        public ?Image $wideLogo = null,
        public ?string $hexBackgroundColor = null,
        public ?Notifications $notifications = null,
        public ?Barcode $barcode = null,
        public ?Image $heroImage = null,
        public ?TimeInterval $validTimeInterval = null,
        public ?array $imageModulesData = null,
        public ?array $textModulesData = null,
        public ?LinksModuleData $linksModuleData = null,
        public ?AppLinkData $appLinkData = null,
        public ?GroupingInfo $groupingInfo = null,
        public ?string $smartTapRedemptionValue = null,
        public ?RotatingBarcode $rotatingBarcode = null,
        public ?StateEnum $state = null,
        public ?array $messages = null,
        public ?PassConstraints $passConstraints = null,
        public ?array $linkedObjectIds = null,
    ) {
    }
}
