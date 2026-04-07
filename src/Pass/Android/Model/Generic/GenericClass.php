<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Generic;

use Jolicode\WalletKit\Pass\Android\Model\Shared\AppLinkData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\CallbackOptions;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ImageModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LinksModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\MerchantLocation;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Message;
use Jolicode\WalletKit\Pass\Android\Model\Shared\MultipleDevicesAndHoldersAllowedStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\NotifyPreferenceEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\SecurityAnimation;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TextModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ValueAddedModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ViewUnlockRequirementEnum;

/**
 * @phpstan-import-type ImageModuleDataType from ImageModuleData
 * @phpstan-import-type TextModuleDataType from TextModuleData
 * @phpstan-import-type LinksModuleDataType from LinksModuleData
 * @phpstan-import-type SecurityAnimationType from SecurityAnimation
 * @phpstan-import-type MultipleDevicesAndHoldersAllowedStatus from MultipleDevicesAndHoldersAllowedStatusEnum
 * @phpstan-import-type CallbackOptionsType from CallbackOptions
 * @phpstan-import-type ViewUnlockRequirement from ViewUnlockRequirementEnum
 * @phpstan-import-type GoogleMessageType from Message
 * @phpstan-import-type AppLinkDataType from AppLinkData
 * @phpstan-import-type MerchantLocationType from MerchantLocation
 * @phpstan-import-type ValueAddedModuleDataType from ValueAddedModuleData
 * @phpstan-import-type NotifyPreference from NotifyPreferenceEnum
 *
 * @phpstan-type GenericClassType array{id: string, imageModulesData?: list<ImageModuleDataType>, textModulesData?: list<TextModuleDataType>, linksModuleData?: LinksModuleDataType, enableSmartTap?: bool, redemptionIssuers?: list<string>, securityAnimation?: SecurityAnimationType, multipleDevicesAndHoldersAllowedStatus?: MultipleDevicesAndHoldersAllowedStatus, callbackOptions?: CallbackOptionsType, viewUnlockRequirement?: ViewUnlockRequirement, messages?: list<GoogleMessageType>, appLinkData?: AppLinkDataType, merchantLocations?: list<MerchantLocationType>, valueAddedModuleData?: list<ValueAddedModuleDataType>, notifyPreference?: NotifyPreference}
 */
class GenericClass
{
    /**
     * @param list<ImageModuleData>|null      $imageModulesData
     * @param list<TextModuleData>|null       $textModulesData
     * @param list<string>|null               $redemptionIssuers
     * @param list<Message>|null              $messages
     * @param list<MerchantLocation>|null     $merchantLocations
     * @param list<ValueAddedModuleData>|null $valueAddedModuleData
     */
    public function __construct(
        public string $id,
        public ?array $imageModulesData = null,
        public ?array $textModulesData = null,
        public ?LinksModuleData $linksModuleData = null,
        public ?bool $enableSmartTap = null,
        public ?array $redemptionIssuers = null,
        public ?SecurityAnimation $securityAnimation = null,
        public ?MultipleDevicesAndHoldersAllowedStatusEnum $multipleDevicesAndHoldersAllowedStatus = null,
        public ?CallbackOptions $callbackOptions = null,
        public ?ViewUnlockRequirementEnum $viewUnlockRequirement = null,
        public ?array $messages = null,
        public ?AppLinkData $appLinkData = null,
        public ?array $merchantLocations = null,
        public ?array $valueAddedModuleData = null,
        public ?NotifyPreferenceEnum $notifyPreference = null,
    ) {
    }
}
