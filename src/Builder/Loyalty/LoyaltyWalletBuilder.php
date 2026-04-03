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
        $applePass = $this->createApplePass(PassTypeEnum::StoreCard, $structure);

        $loyaltyClass = new LoyaltyClass(
            id: $this->context->googleClassId,
            issuerName: $this->context->appleOrganizationName,
            reviewStatus: $this->resolvedGoogleReviewStatus(),
            programName: $this->programName,
            hexBackgroundColor: $this->resolvedGoogleHex(),
            linksModuleData: $this->common->linksModuleData,
            appLinkData: $this->common->appLinkData,
        );

        $loyaltyObject = new LoyaltyObject(
            id: $this->context->googleObjectId,
            classId: $this->context->googleClassId,
            state: $this->resolvedGoogleObjectState(),
            accountName: $this->accountName,
            accountId: $this->accountId,
            barcode: $this->primaryGoogleBarcode(),
            hexBackgroundColor: $this->resolvedGoogleHex(),
            validTimeInterval: $this->common->validTimeInterval,
            linksModuleData: $this->common->linksModuleData,
            appLinkData: $this->common->appLinkData,
            groupingInfo: $this->resolvedGoogleGrouping(),
        );

        return new BuiltWalletPass(
            $applePass,
            new GoogleWalletPair(GoogleVerticalEnum::Loyalty, $loyaltyClass, $loyaltyObject),
        );
    }
}
