<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\EventTicket;

use Jolicode\WalletKit\Common\Color;
use Jolicode\WalletKit\Pass\Android\Model\Shared\AppLinkData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\CallbackOptions;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Image;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ImageModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LinksModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;
use Jolicode\WalletKit\Pass\Android\Model\Shared\MerchantLocation;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Message;
use Jolicode\WalletKit\Pass\Android\Model\Shared\MultipleDevicesAndHoldersAllowedStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\NotifyPreferenceEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Review;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\SecurityAnimation;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TextModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Uri;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ValueAddedModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ViewUnlockRequirementEnum;

/**
 * @phpstan-import-type ReviewStatus from ReviewStatusEnum
 * @phpstan-import-type LocalizedStringType from LocalizedString
 * @phpstan-import-type ImageType from Image
 * @phpstan-import-type UriType from Uri
 * @phpstan-import-type MultipleDevicesAndHoldersAllowedStatus from MultipleDevicesAndHoldersAllowedStatusEnum
 * @phpstan-import-type CallbackOptionsType from CallbackOptions
 * @phpstan-import-type SecurityAnimationType from SecurityAnimation
 * @phpstan-import-type ViewUnlockRequirement from ViewUnlockRequirementEnum
 * @phpstan-import-type GoogleMessageType from Message
 * @phpstan-import-type ImageModuleDataType from ImageModuleData
 * @phpstan-import-type TextModuleDataType from TextModuleData
 * @phpstan-import-type LinksModuleDataType from LinksModuleData
 * @phpstan-import-type AppLinkDataType from AppLinkData
 * @phpstan-import-type MerchantLocationType from MerchantLocation
 * @phpstan-import-type ValueAddedModuleDataType from ValueAddedModuleData
 * @phpstan-import-type NotifyPreference from NotifyPreferenceEnum
 * @phpstan-import-type ReviewType from Review
 *
 * @phpstan-type EventTicketClassType array{
 *     id: string,
 *     issuerName: string,
 *     eventName: string,
 *     reviewStatus: ReviewStatus,
 *     localizedEventName?: LocalizedStringType,
 *     eventId?: string,
 *     localizedIssuerName?: LocalizedStringType,
 *     hexBackgroundColor?: string,
 *     countryCode?: string,
 *     logo?: ImageType,
 *     wideLogo?: ImageType,
 *     heroImage?: ImageType,
 *     venue?: LocalizedStringType,
 *     dateTime?: LocalizedStringType,
 *     finePrint?: string,
 *     localizedFinePrint?: LocalizedStringType,
 *     confirmationCodeLabel?: LocalizedStringType,
 *     customSeatLabel?: LocalizedStringType,
 *     customRowLabel?: LocalizedStringType,
 *     customSectionLabel?: LocalizedStringType,
 *     customGateLabel?: LocalizedStringType,
 *     customConfirmationCodeLabel?: LocalizedStringType,
 *     enableSmartTap?: bool,
 *     redemptionIssuers?: list<string>,
 *     multipleDevicesAndHoldersAllowedStatus?: MultipleDevicesAndHoldersAllowedStatus,
 *     callbackOptions?: CallbackOptionsType,
 *     securityAnimation?: SecurityAnimationType,
 *     viewUnlockRequirement?: ViewUnlockRequirement,
 *     messages?: list<GoogleMessageType>,
 *     imageModulesData?: list<ImageModuleDataType>,
 *     textModulesData?: list<TextModuleDataType>,
 *     linksModuleData?: LinksModuleDataType,
 *     homepageUri?: UriType,
 *     appLinkData?: AppLinkDataType,
 *     merchantLocations?: list<MerchantLocationType>,
 *     valueAddedModuleData?: list<ValueAddedModuleDataType>,
 *     notifyPreference?: NotifyPreference,
 *     review?: ReviewType,
 * }
 */
class EventTicketClass
{
    /**
     * @param list<string>|null               $redemptionIssuers
     * @param list<Message>|null              $messages
     * @param list<ImageModuleData>|null      $imageModulesData
     * @param list<TextModuleData>|null       $textModulesData
     * @param list<MerchantLocation>|null     $merchantLocations
     * @param list<ValueAddedModuleData>|null $valueAddedModuleData
     */
    public function __construct(
        public string $id,
        public string $issuerName,
        public string $eventName,
        public ReviewStatusEnum $reviewStatus,
        public ?LocalizedString $localizedEventName = null,
        public ?string $eventId = null,
        public ?LocalizedString $localizedIssuerName = null,
        public ?Color $hexBackgroundColor = null,
        public ?string $countryCode = null,
        public ?Image $logo = null,
        public ?Image $wideLogo = null,
        public ?Image $heroImage = null,
        public ?LocalizedString $venue = null,
        public ?LocalizedString $dateTime = null,
        public ?string $finePrint = null,
        public ?LocalizedString $localizedFinePrint = null,
        public ?LocalizedString $confirmationCodeLabel = null,
        public ?LocalizedString $customSeatLabel = null,
        public ?LocalizedString $customRowLabel = null,
        public ?LocalizedString $customSectionLabel = null,
        public ?LocalizedString $customGateLabel = null,
        public ?LocalizedString $customConfirmationCodeLabel = null,
        public ?bool $enableSmartTap = null,
        public ?array $redemptionIssuers = null,
        public ?MultipleDevicesAndHoldersAllowedStatusEnum $multipleDevicesAndHoldersAllowedStatus = null,
        public ?CallbackOptions $callbackOptions = null,
        public ?SecurityAnimation $securityAnimation = null,
        public ?ViewUnlockRequirementEnum $viewUnlockRequirement = null,
        public ?array $messages = null,
        public ?array $imageModulesData = null,
        public ?array $textModulesData = null,
        public ?LinksModuleData $linksModuleData = null,
        public ?Uri $homepageUri = null,
        public ?AppLinkData $appLinkData = null,
        public ?array $merchantLocations = null,
        public ?array $valueAddedModuleData = null,
        public ?NotifyPreferenceEnum $notifyPreference = null,
        public ?Review $review = null,
    ) {
    }
}
