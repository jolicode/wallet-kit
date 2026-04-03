<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder\Offer;

use Jolicode\WalletKit\Builder\AbstractWalletBuilder;
use Jolicode\WalletKit\Builder\BuiltWalletPass;
use Jolicode\WalletKit\Builder\GoogleVerticalEnum;
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Pass\Android\Model\Offer\OfferClass;
use Jolicode\WalletKit\Pass\Android\Model\Offer\OfferObject;
use Jolicode\WalletKit\Pass\Android\Model\Offer\RedemptionChannelEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Field;
use Jolicode\WalletKit\Pass\Apple\Model\PassStructure;
use Jolicode\WalletKit\Pass\Apple\Model\PassTypeEnum;

final class OfferWalletBuilder extends AbstractWalletBuilder
{
    public function __construct(
        WalletPlatformContext $context,
        private readonly string $title,
        private readonly string $provider,
        private readonly RedemptionChannelEnum $redemptionChannel,
    ) {
        parent::__construct($context);
    }

    public function build(): BuiltWalletPass
    {
        $structure = new PassStructure(
            primaryFields: [
                new Field(key: 'offer', value: $this->title, label: 'Offer'),
                new Field(key: 'provider', value: $this->provider, label: 'Provider'),
            ],
        );

        $applePass = $this->createApplePass(PassTypeEnum::COUPON, $structure);

        $offerClass = new OfferClass(
            id: $this->context->googleClassId,
            issuerName: $this->context->appleOrganizationName,
            title: $this->title,
            provider: $this->provider,
            redemptionChannel: $this->redemptionChannel,
            reviewStatus: $this->resolvedGoogleReviewStatus(),
            hexBackgroundColor: $this->resolvedGoogleHex(),
            linksModuleData: $this->common->linksModuleData,
            appLinkData: $this->common->appLinkData,
        );

        $offerObject = new OfferObject(
            id: $this->context->googleObjectId,
            classId: $this->context->googleClassId,
            state: $this->resolvedGoogleObjectState(),
            barcode: $this->primaryGoogleBarcode(),
            hexBackgroundColor: $this->resolvedGoogleHex(),
            validTimeInterval: $this->common->validTimeInterval,
            linksModuleData: $this->common->linksModuleData,
            appLinkData: $this->common->appLinkData,
            groupingInfo: $this->resolvedGoogleGrouping(),
        );

        return new BuiltWalletPass(
            $applePass,
            new GoogleWalletPair(GoogleVerticalEnum::OFFER, $offerClass, $offerObject),
        );
    }
}
