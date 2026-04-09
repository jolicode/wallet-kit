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
use Jolicode\WalletKit\Pass\Samsung\Model\Coupon\CouponAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardSubTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardTypeEnum;

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

        $applePass = $this->context->hasApple()
            ? $this->createApplePass(PassTypeEnum::COUPON, $structure)
            : null;

        $googlePair = null;
        if ($this->context->hasGoogle()) {
            $g = $this->context->google;

            $offerClass = new OfferClass(
                id: $g->classId,
                issuerName: $this->context->googleIssuerName(),
                title: $this->title,
                provider: $this->provider,
                redemptionChannel: $this->redemptionChannel,
                reviewStatus: $this->resolvedGoogleReviewStatus(),
                hexBackgroundColor: $this->resolvedGoogleHex(),
                linksModuleData: $this->common->linksModuleData,
                appLinkData: $this->common->appLinkData,
            );

            $offerObject = new OfferObject(
                id: $g->objectId,
                classId: $g->classId,
                state: $this->resolvedGoogleObjectState(),
                barcode: $this->primaryGoogleBarcode(),
                hexBackgroundColor: $this->resolvedGoogleHex(),
                validTimeInterval: $this->common->validTimeInterval,
                linksModuleData: $this->common->linksModuleData,
                appLinkData: $this->common->appLinkData,
                groupingInfo: $this->resolvedGoogleGrouping(),
            );

            $googlePair = new GoogleWalletPair(GoogleVerticalEnum::OFFER, $offerClass, $offerObject);
        }

        $samsungCard = null;
        if ($this->context->hasSamsung()) {
            $s = $this->context->samsung;
            $now = (int) (microtime(true) * 1000);
            $attributes = new CouponAttributes(
                title: $this->title,
                appLinkLogo: $s->appLinkLogo ?? '',
                appLinkName: $s->appLinkName ?? '',
                appLinkData: $s->appLinkData ?? '',
                issueDate: $now,
                expiry: $now + 86400000 * 365,
                brandName: $this->provider,
                barcode: $this->primarySamsungBarcode(),
                bgColor: $this->resolvedSamsungHexColor(),
            );
            $samsungCard = $this->createSamsungCard(CardTypeEnum::COUPON, CardSubTypeEnum::OTHERS, $attributes);
        }

        return new BuiltWalletPass($applePass, $googlePair, $samsungCard);
    }
}
