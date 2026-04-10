<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder\Loyalty;

use Jolicode\WalletKit\Builder\AbstractWalletBuilder;
use Jolicode\WalletKit\Builder\BuiltWalletPass;
use Jolicode\WalletKit\Builder\GoogleVerticalEnum;
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Pass\Android\Model\Loyalty\LoyaltyClass;
use Jolicode\WalletKit\Pass\Android\Model\Loyalty\LoyaltyObject;
use Jolicode\WalletKit\Pass\Apple\Model\Field;
use Jolicode\WalletKit\Pass\Apple\Model\PassStructure;
use Jolicode\WalletKit\Pass\Apple\Model\PassTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\Loyalty\LoyaltyAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardSubTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardTypeEnum;

final class LoyaltyWalletBuilder extends AbstractWalletBuilder
{
    private ?string $accountName = null;

    private ?string $accountId = null;

    public function __construct(
        WalletPlatformContext $context,
        private readonly ?string $programName = null,
    ) {
        parent::__construct($context);
    }

    public function withAccount(?string $accountName, ?string $accountId = null): self
    {
        $this->accountName = $accountName;
        $this->accountId = $accountId;

        return $this;
    }

    public function build(): BuiltWalletPass
    {
        $primaryFields = [];
        if (null !== $this->programName && '' !== $this->programName) {
            $primaryFields[] = new Field(key: 'program', value: $this->programName, label: 'Program');
        }
        if (null !== $this->accountName && '' !== $this->accountName) {
            $primaryFields[] = new Field(key: 'name', value: $this->accountName, label: 'Member');
        }

        $structure = new PassStructure(primaryFields: $primaryFields);
        $applePass = $this->context->hasApple()
            ? $this->createApplePass(PassTypeEnum::STORE_CARD, $structure)
            : null;

        $googlePair = null;
        if ($this->context->hasGoogle()) {
            $g = $this->context->google;

            $loyaltyClass = new LoyaltyClass(
                id: $g->classId,
                issuerName: $this->context->googleIssuerName(),
                reviewStatus: $this->resolvedGoogleReviewStatus(),
                programName: $this->programName,
                hexBackgroundColor: $this->resolvedBackgroundColor(),
                linksModuleData: $this->common->linksModuleData,
                appLinkData: $this->common->appLinkData,
            );

            $loyaltyObject = new LoyaltyObject(
                id: $g->objectId,
                classId: $g->classId,
                state: $this->resolvedGoogleObjectState(),
                accountName: $this->accountName,
                accountId: $this->accountId,
                barcode: $this->primaryGoogleBarcode(),
                hexBackgroundColor: $this->resolvedBackgroundColor(),
                validTimeInterval: $this->common->validTimeInterval,
                linksModuleData: $this->common->linksModuleData,
                appLinkData: $this->common->appLinkData,
                groupingInfo: $this->resolvedGoogleGrouping(),
            );

            $googlePair = new GoogleWalletPair(GoogleVerticalEnum::LOYALTY, $loyaltyClass, $loyaltyObject);
        }

        $samsungCard = null;
        if ($this->context->hasSamsung()) {
            $s = $this->context->samsung;
            $attributes = new LoyaltyAttributes(
                title: $this->programName ?? 'Loyalty',
                providerName: $this->context->hasApple() ? $this->context->apple->organizationName : ($this->context->hasGoogle() ? $this->context->googleIssuerName() : ''),
                appLinkLogo: $s->appLinkLogo ?? '',
                appLinkName: $s->appLinkName ?? '',
                appLinkData: $s->appLinkData ?? '',
                barcode: $this->primarySamsungBarcode(),
                bgColor: $this->resolvedBackgroundColor(),
                merchantName: $this->programName,
            );
            $samsungCard = $this->createSamsungCard(CardTypeEnum::LOYALTY, CardSubTypeEnum::OTHERS, $attributes);
        }

        return new BuiltWalletPass($applePass, $googlePair, $samsungCard);
    }
}
