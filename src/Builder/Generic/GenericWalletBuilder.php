<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder\Generic;

use Jolicode\WalletKit\Builder\AbstractWalletBuilder;
use Jolicode\WalletKit\Builder\BuiltWalletPass;
use Jolicode\WalletKit\Builder\GoogleVerticalEnum;
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Builder\Internal\LocalizedStringHelper;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericClass;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericObject;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericTypeEnum;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Pass\Apple\Model\PassStructure;
use Jolicode\WalletKit\Pass\Apple\Model\PassTypeEnum;

final class GenericWalletBuilder extends AbstractWalletBuilder
{
    private PassStructure $passStructure;

    private ?GenericTypeEnum $genericType = null;

    private ?string $googleCardTitle = null;

    public function __construct(WalletPlatformContext $context)
    {
        parent::__construct($context);
        $this->passStructure = new PassStructure();
    }

    public function withPassStructure(PassStructure $structure): self
    {
        $this->passStructure = $structure;

        return $this;
    }

    public function withGenericType(?GenericTypeEnum $genericType): self
    {
        $this->genericType = $genericType;

        return $this;
    }

    /**
     * Sets the Google generic object card title (localized EN).
     */
    public function withGoogleCardTitle(?string $title): self
    {
        $this->googleCardTitle = $title;

        return $this;
    }

    public function build(): BuiltWalletPass
    {
        $applePass = $this->createApplePass(PassTypeEnum::Generic, $this->passStructure);

        $googleClass = new GenericClass(
            id: $this->context->googleClassId,
            appLinkData: $this->common->appLinkData,
            linksModuleData: $this->common->linksModuleData,
        );

        $cardTitle = null !== $this->googleCardTitle
            ? LocalizedStringHelper::en($this->googleCardTitle)
            : null;

        $googleObject = new GenericObject(
            id: $this->context->googleObjectId,
            classId: $this->context->googleClassId,
            genericType: $this->genericType,
            cardTitle: $cardTitle,
            hexBackgroundColor: $this->resolvedGoogleHex(),
            barcode: $this->primaryGoogleBarcode(),
            validTimeInterval: $this->common->validTimeInterval,
            linksModuleData: $this->common->linksModuleData,
            appLinkData: $this->common->appLinkData,
            groupingInfo: $this->resolvedGoogleGrouping(),
            state: $this->resolvedGoogleObjectState(),
        );

        return new BuiltWalletPass(
            $applePass,
            new GoogleWalletPair(GoogleVerticalEnum::Generic, $googleClass, $googleObject),
        );
    }
}
