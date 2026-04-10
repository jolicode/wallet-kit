<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder\GiftCard;

use Jolicode\WalletKit\Builder\AbstractWalletBuilder;
use Jolicode\WalletKit\Builder\BuiltWalletPass;
use Jolicode\WalletKit\Builder\GoogleVerticalEnum;
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Pass\Android\Model\GiftCard\GiftCardClass;
use Jolicode\WalletKit\Pass\Android\Model\GiftCard\GiftCardObject;
use Jolicode\WalletKit\Pass\Apple\Model\Field;
use Jolicode\WalletKit\Pass\Apple\Model\PassStructure;
use Jolicode\WalletKit\Pass\Apple\Model\PassTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\GiftCard\GiftCardAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardSubTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardTypeEnum;

/**
 * Google Wallet gift cards map to Apple {@see PassTypeEnum::STORE_CARD} payloads; Apple has no dedicated gift-card pass type.
 */
final class GiftCardWalletBuilder extends AbstractWalletBuilder
{
    private ?string $pin = null;

    public function __construct(
        WalletPlatformContext $context,
        private readonly string $cardNumber,
    ) {
        parent::__construct($context);
    }

    public function withPin(?string $pin): self
    {
        $this->pin = $pin;

        return $this;
    }

    public function build(): BuiltWalletPass
    {
        $secondaryFields = [];
        if (null !== $this->pin && '' !== $this->pin) {
            $secondaryFields[] = new Field(key: 'pin', value: $this->pin, label: 'PIN');
        }

        $structure = new PassStructure(
            primaryFields: [
                new Field(key: 'card', value: $this->cardNumber, label: 'Card'),
            ],
            secondaryFields: $secondaryFields,
        );

        $applePass = $this->context->hasApple()
            ? $this->createApplePass(PassTypeEnum::STORE_CARD, $structure)
            : null;

        $googlePair = null;
        if ($this->context->hasGoogle()) {
            $g = $this->context->google;

            $giftClass = new GiftCardClass(
                id: $g->classId,
                issuerName: $this->context->googleIssuerName(),
                reviewStatus: $this->resolvedGoogleReviewStatus(),
                hexBackgroundColor: $this->resolvedBackgroundColor(),
                linksModuleData: $this->common->linksModuleData,
                appLinkData: $this->common->appLinkData,
            );

            $giftObject = new GiftCardObject(
                id: $g->objectId,
                classId: $g->classId,
                state: $this->resolvedGoogleObjectState(),
                cardNumber: $this->cardNumber,
                pin: $this->pin,
                barcode: $this->primaryGoogleBarcode(),
                hexBackgroundColor: $this->resolvedBackgroundColor(),
                validTimeInterval: $this->common->validTimeInterval,
                linksModuleData: $this->common->linksModuleData,
                appLinkData: $this->common->appLinkData,
                groupingInfo: $this->resolvedGoogleGrouping(),
            );

            $googlePair = new GoogleWalletPair(GoogleVerticalEnum::GIFT_CARD, $giftClass, $giftObject);
        }

        $samsungCard = null;
        if ($this->context->hasSamsung()) {
            $s = $this->context->samsung;
            $attributes = new GiftCardAttributes(
                title: 'Gift Card',
                providerName: $this->context->hasApple() ? $this->context->apple->organizationName : ($this->context->hasGoogle() ? $this->context->googleIssuerName() : ''),
                appLinkLogo: $s->appLinkLogo ?? '',
                appLinkName: $s->appLinkName ?? '',
                appLinkData: $s->appLinkData ?? '',
                barcode: $this->primarySamsungBarcode(),
                bgColor: $this->resolvedBackgroundColor(),
                amount: $this->cardNumber,
            );
            $samsungCard = $this->createSamsungCard(CardTypeEnum::GIFT_CARD, CardSubTypeEnum::OTHERS, $attributes);
        }

        return new BuiltWalletPass($applePass, $googlePair, $samsungCard);
    }
}
