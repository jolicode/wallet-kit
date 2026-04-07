<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\GiftCard;

use Jolicode\WalletKit\Pass\Android\Model\Shared\AppLinkData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Barcode;
use Jolicode\WalletKit\Pass\Android\Model\Shared\GoogleDateTime;
use Jolicode\WalletKit\Pass\Android\Model\Shared\GroupingInfo;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Image;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ImageModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LinksModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\MerchantLocation;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Message;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Money;
use Jolicode\WalletKit\Pass\Android\Model\Shared\NotifyPreferenceEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\PassConstraints;
use Jolicode\WalletKit\Pass\Android\Model\Shared\RotatingBarcode;
use Jolicode\WalletKit\Pass\Android\Model\Shared\SaveRestrictions;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TextModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TimeInterval;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ValueAddedModuleData;

/**
 * @phpstan-import-type State from StateEnum
 * @phpstan-import-type GoogleBarcodeType from Barcode
 * @phpstan-import-type MoneyType from Money
 * @phpstan-import-type GoogleDateTimeType from GoogleDateTime
 * @phpstan-import-type GoogleMessageType from Message
 * @phpstan-import-type TimeIntervalType from TimeInterval
 * @phpstan-import-type ImageModuleDataType from ImageModuleData
 * @phpstan-import-type TextModuleDataType from TextModuleData
 * @phpstan-import-type LinksModuleDataType from LinksModuleData
 * @phpstan-import-type AppLinkDataType from AppLinkData
 * @phpstan-import-type RotatingBarcodeType from RotatingBarcode
 * @phpstan-import-type ImageType from Image
 * @phpstan-import-type GroupingInfoType from GroupingInfo
 * @phpstan-import-type PassConstraintsType from PassConstraints
 * @phpstan-import-type MerchantLocationType from MerchantLocation
 * @phpstan-import-type ValueAddedModuleDataType from ValueAddedModuleData
 * @phpstan-import-type SaveRestrictionsType from SaveRestrictions
 * @phpstan-import-type NotifyPreference from NotifyPreferenceEnum
 *
 * @phpstan-type GiftCardObjectType array{
 *     id: string,
 *     classId: string,
 *     state: State,
 *     cardNumber: string,
 *     pin?: string,
 *     balance?: MoneyType,
 *     balanceUpdateTime?: GoogleDateTimeType,
 *     eventNumber?: string,
 *     barcode?: GoogleBarcodeType,
 *     hexBackgroundColor?: string,
 *     messages?: list<GoogleMessageType>,
 *     validTimeInterval?: TimeIntervalType,
 *     smartTapRedemptionValue?: string,
 *     disableExpirationNotification?: bool,
 *     imageModulesData?: list<ImageModuleDataType>,
 *     textModulesData?: list<TextModuleDataType>,
 *     linksModuleData?: LinksModuleDataType,
 *     appLinkData?: AppLinkDataType,
 *     rotatingBarcode?: RotatingBarcodeType,
 *     heroImage?: ImageType,
 *     groupingInfo?: GroupingInfoType,
 *     passConstraints?: PassConstraintsType,
 *     linkedObjectIds?: list<string>,
 *     merchantLocations?: list<MerchantLocationType>,
 *     valueAddedModuleData?: list<ValueAddedModuleDataType>,
 *     saveRestrictions?: SaveRestrictionsType,
 *     notifyPreference?: NotifyPreference,
 * }
 */
class GiftCardObject
{
    /**
     * @param list<Message>|null              $messages
     * @param list<ImageModuleData>|null      $imageModulesData
     * @param list<TextModuleData>|null       $textModulesData
     * @param list<string>|null               $linkedObjectIds
     * @param list<MerchantLocation>|null     $merchantLocations
     * @param list<ValueAddedModuleData>|null $valueAddedModuleData
     */
    public function __construct(
        public string $id,
        public string $classId,
        public StateEnum $state,
        public string $cardNumber,
        public ?string $pin = null,
        public ?Money $balance = null,
        public ?GoogleDateTime $balanceUpdateTime = null,
        public ?string $eventNumber = null,
        public ?Barcode $barcode = null,
        public ?string $hexBackgroundColor = null,
        public ?array $messages = null,
        public ?TimeInterval $validTimeInterval = null,
        public ?string $smartTapRedemptionValue = null,
        public ?bool $disableExpirationNotification = null,
        public ?array $imageModulesData = null,
        public ?array $textModulesData = null,
        public ?LinksModuleData $linksModuleData = null,
        public ?AppLinkData $appLinkData = null,
        public ?RotatingBarcode $rotatingBarcode = null,
        public ?Image $heroImage = null,
        public ?GroupingInfo $groupingInfo = null,
        public ?PassConstraints $passConstraints = null,
        public ?array $linkedObjectIds = null,
        public ?array $merchantLocations = null,
        public ?array $valueAddedModuleData = null,
        public ?SaveRestrictions $saveRestrictions = null,
        public ?NotifyPreferenceEnum $notifyPreference = null,
    ) {
    }
}
