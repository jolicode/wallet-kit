<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

use Jolicode\WalletKit\Pass\Android\Model\Shared\AppLinkData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Barcode;
use Jolicode\WalletKit\Pass\Android\Model\Shared\GroupingInfo;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Image;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ImageModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LinksModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;
use Jolicode\WalletKit\Pass\Android\Model\Shared\MerchantLocation;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Message;
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
 * @phpstan-import-type TripType from TripTypeEnum
 * @phpstan-import-type PassengerType from PassengerTypeEnum
 * @phpstan-import-type TicketStatus from TicketStatusEnum
 * @phpstan-import-type LocalizedStringType from LocalizedString
 * @phpstan-import-type ConcessionCategory from ConcessionCategoryEnum
 * @phpstan-import-type TicketRestrictionsType from TicketRestrictions
 * @phpstan-import-type PurchaseDetailsType from PurchaseDetails
 * @phpstan-import-type TicketLegType from TicketLeg
 * @phpstan-import-type GoogleBarcodeType from Barcode
 * @phpstan-import-type GoogleMessageType from Message
 * @phpstan-import-type TimeIntervalType from TimeInterval
 * @phpstan-import-type ImageModuleDataType from ImageModuleData
 * @phpstan-import-type TextModuleDataType from TextModuleData
 * @phpstan-import-type LinksModuleDataType from LinksModuleData
 * @phpstan-import-type AppLinkDataType from AppLinkData
 * @phpstan-import-type ActivationStatusType from ActivationStatus
 * @phpstan-import-type RotatingBarcodeType from RotatingBarcode
 * @phpstan-import-type ImageType from Image
 * @phpstan-import-type GroupingInfoType from GroupingInfo
 * @phpstan-import-type PassConstraintsType from PassConstraints
 * @phpstan-import-type MerchantLocationType from MerchantLocation
 * @phpstan-import-type ValueAddedModuleDataType from ValueAddedModuleData
 * @phpstan-import-type SaveRestrictionsType from SaveRestrictions
 * @phpstan-import-type NotifyPreference from NotifyPreferenceEnum
 *
 * @phpstan-type TransitObjectType array{id: string, classId: string, state: State, tripType: TripType, ticketNumber?: string, passengerType?: PassengerType, passengerNames?: string, tripId?: string, ticketStatus?: TicketStatus, customTicketStatus?: LocalizedStringType, concessionCategory?: ConcessionCategory, customConcessionCategory?: LocalizedStringType, ticketRestrictions?: TicketRestrictionsType, purchaseDetails?: PurchaseDetailsType, ticketLeg?: TicketLegType, ticketLegs?: list<TicketLegType>, hexBackgroundColor?: string, barcode?: GoogleBarcodeType, messages?: list<GoogleMessageType>, validTimeInterval?: TimeIntervalType, smartTapRedemptionValue?: string, disableExpirationNotification?: bool, imageModulesData?: list<ImageModuleDataType>, textModulesData?: list<TextModuleDataType>, linksModuleData?: LinksModuleDataType, appLinkData?: AppLinkDataType, activationStatus?: ActivationStatusType, rotatingBarcode?: RotatingBarcodeType, heroImage?: ImageType, groupingInfo?: GroupingInfoType, passConstraints?: PassConstraintsType, linkedObjectIds?: list<string>, merchantLocations?: list<MerchantLocationType>, valueAddedModuleData?: list<ValueAddedModuleDataType>, saveRestrictions?: SaveRestrictionsType, notifyPreference?: NotifyPreference}
 */
class TransitObject
{
    /**
     * @param list<TicketLeg>|null            $ticketLegs
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
        public TripTypeEnum $tripType,
        public ?string $ticketNumber = null,
        public ?PassengerTypeEnum $passengerType = null,
        public ?string $passengerNames = null,
        public ?string $tripId = null,
        public ?TicketStatusEnum $ticketStatus = null,
        public ?LocalizedString $customTicketStatus = null,
        public ?ConcessionCategoryEnum $concessionCategory = null,
        public ?LocalizedString $customConcessionCategory = null,
        public ?TicketRestrictions $ticketRestrictions = null,
        public ?PurchaseDetails $purchaseDetails = null,
        public ?TicketLeg $ticketLeg = null,
        public ?array $ticketLegs = null,
        public ?string $hexBackgroundColor = null,
        public ?Barcode $barcode = null,
        public ?array $messages = null,
        public ?TimeInterval $validTimeInterval = null,
        public ?string $smartTapRedemptionValue = null,
        public ?bool $disableExpirationNotification = null,
        public ?array $imageModulesData = null,
        public ?array $textModulesData = null,
        public ?LinksModuleData $linksModuleData = null,
        public ?AppLinkData $appLinkData = null,
        public ?ActivationStatus $activationStatus = null,
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
