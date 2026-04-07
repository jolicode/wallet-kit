<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

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
 * @phpstan-import-type TransitType from TransitTypeEnum
 * @phpstan-import-type LocalizedStringType from LocalizedString
 * @phpstan-import-type ImageType from Image
 * @phpstan-import-type MultipleDevicesAndHoldersAllowedStatus from MultipleDevicesAndHoldersAllowedStatusEnum
 * @phpstan-import-type CallbackOptionsType from CallbackOptions
 * @phpstan-import-type SecurityAnimationType from SecurityAnimation
 * @phpstan-import-type ViewUnlockRequirement from ViewUnlockRequirementEnum
 * @phpstan-import-type GoogleMessageType from Message
 * @phpstan-import-type ImageModuleDataType from ImageModuleData
 * @phpstan-import-type TextModuleDataType from TextModuleData
 * @phpstan-import-type LinksModuleDataType from LinksModuleData
 * @phpstan-import-type UriType from Uri
 * @phpstan-import-type AppLinkDataType from AppLinkData
 * @phpstan-import-type MerchantLocationType from MerchantLocation
 * @phpstan-import-type ValueAddedModuleDataType from ValueAddedModuleData
 * @phpstan-import-type NotifyPreference from NotifyPreferenceEnum
 * @phpstan-import-type ReviewType from Review
 *
 * @phpstan-type TransitClassType array{id: string, issuerName: string, reviewStatus: ReviewStatus, transitType: TransitType, localizedIssuerName?: LocalizedStringType, transitOperatorName?: LocalizedStringType, localizedTransitOperatorName?: LocalizedStringType, logo?: ImageType, wideLogo?: ImageType, hexBackgroundColor?: string, countryCode?: string, heroImage?: ImageType, enableSmartTap?: bool, redemptionIssuers?: list<string>, multipleDevicesAndHoldersAllowedStatus?: MultipleDevicesAndHoldersAllowedStatus, callbackOptions?: CallbackOptionsType, securityAnimation?: SecurityAnimationType, viewUnlockRequirement?: ViewUnlockRequirement, messages?: list<GoogleMessageType>, imageModulesData?: list<ImageModuleDataType>, textModulesData?: list<TextModuleDataType>, linksModuleData?: LinksModuleDataType, homepageUri?: UriType, appLinkData?: AppLinkDataType, enableSingleLegItinerary?: bool, languageOverride?: string, customTransitTypeLabel?: LocalizedStringType, merchantLocations?: list<MerchantLocationType>, valueAddedModuleData?: list<ValueAddedModuleDataType>, notifyPreference?: NotifyPreference, review?: ReviewType}
 */
class TransitClass
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
        public ReviewStatusEnum $reviewStatus,
        public TransitTypeEnum $transitType,
        public ?LocalizedString $localizedIssuerName = null,
        public ?LocalizedString $transitOperatorName = null,
        public ?LocalizedString $localizedTransitOperatorName = null,
        public ?Image $logo = null,
        public ?Image $wideLogo = null,
        public ?string $hexBackgroundColor = null,
        public ?string $countryCode = null,
        public ?Image $heroImage = null,
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
        public ?bool $enableSingleLegItinerary = null,
        public ?string $languageOverride = null,
        public ?LocalizedString $customTransitTypeLabel = null,
        public ?array $merchantLocations = null,
        public ?array $valueAddedModuleData = null,
        public ?NotifyPreferenceEnum $notifyPreference = null,
        public ?Review $review = null,
    ) {
    }
}
