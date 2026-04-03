<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder;

use Jolicode\WalletKit\Builder\Internal\BarcodeMapper;
use Jolicode\WalletKit\Builder\Internal\ColorMapper;
use Jolicode\WalletKit\Builder\Internal\CommonWalletState;
use Jolicode\WalletKit\Pass\Android\Model\Shared\AppLinkData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Barcode as GoogleBarcode;
use Jolicode\WalletKit\Pass\Android\Model\Shared\GoogleDateTime;
use Jolicode\WalletKit\Pass\Android\Model\Shared\GroupingInfo;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LinksModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TimeInterval;
use Jolicode\WalletKit\Pass\Apple\Model\Barcode as AppleBarcode;
use Jolicode\WalletKit\Pass\Apple\Model\Pass;

/**
 * Portable wallet options shared across vertical builders.
 *
 * @property CommonWalletState $common
 * @property WalletPlatformContext $context
 */
trait CommonWalletBuilderTrait
{
    public function addAppleBarcode(AppleBarcode $barcode): static
    {
        $this->common->appleBarcodes[] = $barcode;

        return $this;
    }

    /**
     * Overrides the Google object barcode instead of deriving it from the first Apple barcode.
     */
    public function withGoogleBarcodeOverride(?GoogleBarcode $barcode): static
    {
        $this->common->googleBarcodeOverride = $barcode;

        return $this;
    }

    public function withAppleBackgroundColor(?string $color): static
    {
        $this->common->appleBackgroundColor = $color;

        return $this;
    }

    public function withGoogleHexBackgroundColor(?string $hex): static
    {
        $this->common->googleHexBackgroundColor = $hex;

        return $this;
    }

    /**
     * Sets Apple background from RGB and, when parsable, derives Google hex automatically unless hex was set explicitly.
     */
    public function withBackgroundColorRgb(string $appleRgb): static
    {
        $this->common->appleBackgroundColor = $appleRgb;
        if (null === $this->common->googleHexBackgroundColor) {
            $this->common->googleHexBackgroundColor = ColorMapper::appleRgbToGoogleHex($appleRgb);
        }

        return $this;
    }

    public function withAppleForegroundColor(?string $color): static
    {
        $this->common->appleForegroundColor = $color;

        return $this;
    }

    public function withAppleLabelColor(?string $color): static
    {
        $this->common->appleLabelColor = $color;

        return $this;
    }

    public function withGrouping(?string $groupingIdentifier, ?int $sortIndex = null): static
    {
        $this->common->groupingIdentifier = $groupingIdentifier;
        $this->common->groupingSortIndex = $sortIndex;

        return $this;
    }

    public function withAppleWebService(?string $url, ?string $authenticationToken = null): static
    {
        $this->common->webServiceURL = $url;
        $this->common->authenticationToken = $authenticationToken;

        return $this;
    }

    public function withAppleAppLaunchUrl(?string $url): static
    {
        $this->common->appLaunchURL = $url;

        return $this;
    }

    /**
     * @param list<int> $identifiers
     */
    public function withAppleAssociatedStoreIdentifiers(array $identifiers): static
    {
        $this->common->associatedStoreIdentifiers = $identifiers;

        return $this;
    }

    public function withAppleExpiration(?string $w3cDate, ?bool $voided = null): static
    {
        $this->common->appleExpirationDate = $w3cDate;
        $this->common->appleVoided = $voided;

        return $this;
    }

    public function withGoogleValidTimeInterval(?TimeInterval $interval): static
    {
        $this->common->validTimeInterval = $interval;

        return $this;
    }

    /**
     * Convenience helper for Google {@see TimeInterval} using ISO-8601 date strings.
     */
    public function withGoogleValidityWindow(?string $startIsoDate, ?string $endIsoDate): static
    {
        $this->common->validTimeInterval = new TimeInterval(
            null !== $startIsoDate ? new GoogleDateTime($startIsoDate) : null,
            null !== $endIsoDate ? new GoogleDateTime($endIsoDate) : null,
        );

        return $this;
    }

    public function withGoogleReviewStatus(ReviewStatusEnum $status): static
    {
        $this->common->googleReviewStatus = $status;

        return $this;
    }

    public function withGoogleObjectState(StateEnum $state): static
    {
        $this->common->googleObjectState = $state;

        return $this;
    }

    public function withAppLinkData(?AppLinkData $appLinkData): static
    {
        $this->common->appLinkData = $appLinkData;

        return $this;
    }

    public function withGoogleLinksModuleData(?LinksModuleData $linksModuleData): static
    {
        $this->common->linksModuleData = $linksModuleData;

        return $this;
    }

    /**
     * @param callable(Pass): void $mutator
     */
    public function mutateApple(callable $mutator): static
    {
        $this->common->applePassMutator = $mutator;

        return $this;
    }

    protected function primaryGoogleBarcode(): ?GoogleBarcode
    {
        if (null !== $this->common->googleBarcodeOverride) {
            return $this->common->googleBarcodeOverride;
        }

        return BarcodeMapper::fromFirstAppleBarcode($this->common->appleBarcodes);
    }

    protected function resolvedGoogleHex(): ?string
    {
        return $this->common->googleHexBackgroundColor
            ?? ColorMapper::appleRgbToGoogleHex($this->common->appleBackgroundColor);
    }

    protected function resolvedGoogleReviewStatus(): ReviewStatusEnum
    {
        return $this->common->googleReviewStatus ?? $this->context->defaultGoogleReviewStatus;
    }

    protected function resolvedGoogleObjectState(): StateEnum
    {
        return $this->common->googleObjectState ?? $this->context->defaultGoogleObjectState;
    }

    protected function resolvedGoogleGrouping(): ?GroupingInfo
    {
        if (null === $this->common->groupingIdentifier) {
            return null;
        }

        return new GroupingInfo($this->common->groupingIdentifier, $this->common->groupingSortIndex);
    }

    protected function finishApplePass(Pass $pass): Pass
    {
        if (null !== $this->common->applePassMutator) {
            ($this->common->applePassMutator)($pass);
        }

        return $pass;
    }

    /**
     * Builds the Apple {@see Pass} shell shared by all verticals (structure and type are supplied by the caller).
     */
    protected function createApplePass(\Jolicode\WalletKit\Pass\Apple\Model\PassTypeEnum $passType, \Jolicode\WalletKit\Pass\Apple\Model\PassStructure $structure): Pass
    {
        $pass = new Pass(
            description: $this->context->appleDescription,
            organizationName: $this->context->appleOrganizationName,
            teamIdentifier: $this->context->appleTeamIdentifier,
            passTypeIdentifier: $this->context->applePassTypeIdentifier,
            formatVersion: $this->context->appleFormatVersion,
            serialNumber: $this->context->appleSerialNumber,
            passType: $passType,
            structure: $structure,
            barcodes: $this->common->appleBarcodes,
            associatedStoreIdentifiers: $this->common->associatedStoreIdentifiers,
            appLaunchURL: $this->common->appLaunchURL,
            webServiceURL: $this->common->webServiceURL,
            authenticationToken: $this->common->authenticationToken,
            backgroundColor: $this->common->appleBackgroundColor,
            foregroundColor: $this->common->appleForegroundColor,
            labelColor: $this->common->appleLabelColor,
            groupingIdentifier: $this->common->groupingIdentifier,
            expirationDate: $this->common->appleExpirationDate,
            voided: $this->common->appleVoided,
        );

        return $this->finishApplePass($pass);
    }
}
