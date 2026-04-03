<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\GiftCard;

use Jolicode\WalletKit\Pass\Android\Model\Shared\AppLinkData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\CallbackOptions;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Image;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ImageModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LinksModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Message;
use Jolicode\WalletKit\Pass\Android\Model\Shared\MultipleDevicesAndHoldersAllowedStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\SecurityAnimation;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TextModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Uri;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ViewUnlockRequirementEnum;

/**
 * @phpstan-import-type ReviewStatus from ReviewStatusEnum
 * @phpstan-import-type ImageType from Image
 * @phpstan-import-type LocalizedStringType from LocalizedString
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
 *
 * @phpstan-type GiftCardClassType array{
 *     id: string,
 *     issuerName: string,
 *     reviewStatus: ReviewStatus,
 *     merchantName?: string,
 *     programLogo?: ImageType,
 *     wideProgramLogo?: ImageType,
 *     pinLabel?: string,
 *     eventNumberLabel?: string,
 *     allowBarcodeRedemption?: bool,
 *     localizedMerchantName?: LocalizedStringType,
 *     localizedPinLabel?: LocalizedStringType,
 *     localizedEventNumberLabel?: LocalizedStringType,
 *     cardNumberLabel?: string,
 *     localizedCardNumberLabel?: LocalizedStringType,
 *     hexBackgroundColor?: string,
 *     localizedIssuerName?: LocalizedStringType,
 *     countryCode?: string,
 *     heroImage?: ImageType,
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
 * }
 */
class GiftCardClass
{
    /**
     * @param list<string>|null          $redemptionIssuers
     * @param list<Message>|null         $messages
     * @param list<ImageModuleData>|null $imageModulesData
     * @param list<TextModuleData>|null  $textModulesData
     */
    public function __construct(
        public string $id,
        public string $issuerName,
        public ReviewStatusEnum $reviewStatus,
        public ?string $merchantName = null,
        public ?Image $programLogo = null,
        public ?Image $wideProgramLogo = null,
        public ?string $pinLabel = null,
        public ?string $eventNumberLabel = null,
        public ?bool $allowBarcodeRedemption = null,
        public ?LocalizedString $localizedMerchantName = null,
        public ?LocalizedString $localizedPinLabel = null,
        public ?LocalizedString $localizedEventNumberLabel = null,
        public ?string $cardNumberLabel = null,
        public ?LocalizedString $localizedCardNumberLabel = null,
        public ?string $hexBackgroundColor = null,
        public ?LocalizedString $localizedIssuerName = null,
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
    ) {
    }
}
