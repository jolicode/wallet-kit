<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder\Generic;

use Jolicode\WalletKit\Builder\AbstractWalletBuilder;
use Jolicode\WalletKit\Builder\BuiltWalletPass;
use Jolicode\WalletKit\Builder\GoogleVerticalEnum;
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Builder\Internal\LocalizedStringHelper;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericClass;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericObject;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericTypeEnum;
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
        $applePass = $this->context->hasApple()
            ? $this->createApplePass(PassTypeEnum::GENERIC, $this->passStructure)
            : null;

        $googlePair = null;
        if ($this->context->hasGoogle()) {
            $g = $this->context->google;

            $googleClass = new GenericClass(
                id: $g->classId,
                appLinkData: $this->common->appLinkData,
                linksModuleData: $this->common->linksModuleData,
            );

            $cardTitle = null !== $this->googleCardTitle
                ? LocalizedStringHelper::en($this->googleCardTitle)
                : null;

            $googleObject = new GenericObject(
                id: $g->objectId,
                classId: $g->classId,
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

            $googlePair = new GoogleWalletPair(GoogleVerticalEnum::GENERIC, $googleClass, $googleObject);
        }

        return new BuiltWalletPass($applePass, $googlePair);
    }
}
