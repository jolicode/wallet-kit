<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder;

use Jolicode\WalletKit\Builder\Internal\BarcodeMapper;
use Jolicode\WalletKit\Builder\Internal\CommonWalletState;
use Jolicode\WalletKit\Builder\Internal\SamsungBarcodeMapper;
use Jolicode\WalletKit\Common\Color;
use Jolicode\WalletKit\Exception\ApplePlatformContextRequiredException;
use Jolicode\WalletKit\Exception\GooglePlatformContextRequiredException;
use Jolicode\WalletKit\Exception\SamsungPlatformContextRequiredException;
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
use Jolicode\WalletKit\Pass\Samsung\Model\Card;
use Jolicode\WalletKit\Pass\Samsung\Model\CardData;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardSubTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungBarcode;

/**
 * Portable wallet options shared across vertical builders.
 *
 * @property CommonWalletState     $common
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

    public function withBackgroundColor(?Color $color): static
    {
        $this->common->backgroundColor = $color;

        return $this;
    }

    public function withForegroundColor(?Color $color): static
    {
        $this->common->foregroundColor = $color;

        return $this;
    }

    public function withLabelColor(?Color $color): static
    {
        $this->common->labelColor = $color;

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
        if (null !== $url && null === $authenticationToken) {
            $authenticationToken = hash('xxh128', random_bytes(10));
        }
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

    /**
     * @param callable(Card): void $mutator
     */
    public function mutateSamsung(callable $mutator): static
    {
        $this->common->samsungCardMutator = $mutator;

        return $this;
    }

    protected function primaryGoogleBarcode(): ?GoogleBarcode
    {
        if (null !== $this->common->googleBarcodeOverride) {
            return $this->common->googleBarcodeOverride;
        }

        return BarcodeMapper::fromFirstAppleBarcode($this->common->appleBarcodes);
    }

    protected function resolvedBackgroundColor(): ?Color
    {
        return $this->common->backgroundColor;
    }

    protected function resolvedGoogleReviewStatus(): ReviewStatusEnum
    {
        $google = $this->context->google;
        if (null === $google) {
            throw new GooglePlatformContextRequiredException('resolvedGoogleReviewStatus() requires a Google context.');
        }

        return $this->common->googleReviewStatus ?? $google->defaultReviewStatus;
    }

    protected function resolvedGoogleObjectState(): StateEnum
    {
        $google = $this->context->google;
        if (null === $google) {
            throw new GooglePlatformContextRequiredException('resolvedGoogleObjectState() requires a Google context.');
        }

        return $this->common->googleObjectState ?? $google->defaultObjectState;
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
        $apple = $this->context->apple;
        if (null === $apple) {
            throw new ApplePlatformContextRequiredException('createApplePass() requires an Apple context.');
        }

        $pass = new Pass(
            description: $apple->description,
            organizationName: $apple->organizationName,
            teamIdentifier: $apple->teamIdentifier,
            passTypeIdentifier: $apple->passTypeIdentifier,
            formatVersion: $apple->formatVersion,
            serialNumber: $apple->serialNumber,
            passType: $passType,
            structure: $structure,
            barcodes: $this->common->appleBarcodes,
            associatedStoreIdentifiers: $this->common->associatedStoreIdentifiers,
            appLaunchURL: $this->common->appLaunchURL,
            webServiceURL: $this->common->webServiceURL,
            authenticationToken: $this->common->authenticationToken,
            backgroundColor: $this->common->backgroundColor,
            foregroundColor: $this->common->foregroundColor,
            labelColor: $this->common->labelColor,
            groupingIdentifier: $this->common->groupingIdentifier,
            expirationDate: $this->common->appleExpirationDate,
            voided: $this->common->appleVoided,
        );

        return $this->finishApplePass($pass);
    }

    protected function primarySamsungBarcode(): ?SamsungBarcode
    {
        return SamsungBarcodeMapper::fromFirstAppleBarcode($this->common->appleBarcodes);
    }

    /**
     * Builds the Samsung {@see Card} envelope shared by all verticals.
     */
    protected function createSamsungCard(CardTypeEnum $type, CardSubTypeEnum $subType, object $attributes): Card
    {
        $samsung = $this->context->samsung;
        if (null === $samsung) {
            throw new SamsungPlatformContextRequiredException('createSamsungCard() requires a Samsung context.');
        }

        $now = (int) (microtime(true) * 1000);

        $card = new Card(
            type: $type,
            subType: $subType,
            data: [
                new CardData(
                    refId: $samsung->refId,
                    createdAt: $now,
                    updatedAt: $now,
                    language: $samsung->language,
                    attributes: $attributes,
                ),
            ],
        );

        return $this->finishSamsungCard($card);
    }

    protected function finishSamsungCard(Card $card): Card
    {
        if (null !== $this->common->samsungCardMutator) {
            ($this->common->samsungCardMutator)($card);
        }

        return $card;
    }
}
