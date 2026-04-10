<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder\EventTicket;

use Jolicode\WalletKit\Builder\AbstractWalletBuilder;
use Jolicode\WalletKit\Builder\BuiltWalletPass;
use Jolicode\WalletKit\Builder\GoogleVerticalEnum;
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Pass\Android\Model\EventTicket\EventTicketClass;
use Jolicode\WalletKit\Pass\Android\Model\EventTicket\EventTicketObject;
use Jolicode\WalletKit\Pass\Apple\Model\Field;
use Jolicode\WalletKit\Pass\Apple\Model\PassStructure;
use Jolicode\WalletKit\Pass\Apple\Model\PassTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\EventTicket\EventTicketAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardSubTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardTypeEnum;

final class EventTicketWalletBuilder extends AbstractWalletBuilder
{
    private ?string $ticketHolderName = null;

    private ?string $ticketNumber = null;

    public function __construct(
        WalletPlatformContext $context,
        private readonly string $eventName,
    ) {
        parent::__construct($context);
    }

    public function withTicketHolderName(?string $name): self
    {
        $this->ticketHolderName = $name;

        return $this;
    }

    public function withTicketNumber(?string $number): self
    {
        $this->ticketNumber = $number;

        return $this;
    }

    public function build(): BuiltWalletPass
    {
        $secondaryFields = [];
        if (null !== $this->ticketHolderName && '' !== $this->ticketHolderName) {
            $secondaryFields[] = new Field(key: 'holder', value: $this->ticketHolderName, label: 'Holder');
        }
        if (null !== $this->ticketNumber && '' !== $this->ticketNumber) {
            $secondaryFields[] = new Field(key: 'ticket', value: $this->ticketNumber, label: 'Ticket');
        }

        $structure = new PassStructure(
            primaryFields: [
                new Field(key: 'event', value: $this->eventName, label: 'Event'),
            ],
            secondaryFields: $secondaryFields,
        );

        $applePass = $this->context->hasApple()
            ? $this->createApplePass(PassTypeEnum::EVENT_TICKET, $structure)
            : null;

        $googlePair = null;
        if ($this->context->hasGoogle()) {
            $g = $this->context->google;

            $eventClass = new EventTicketClass(
                id: $g->classId,
                issuerName: $this->context->googleIssuerName(),
                eventName: $this->eventName,
                reviewStatus: $this->resolvedGoogleReviewStatus(),
                hexBackgroundColor: $this->resolvedBackgroundColor(),
                linksModuleData: $this->common->linksModuleData,
                appLinkData: $this->common->appLinkData,
            );

            $eventObject = new EventTicketObject(
                id: $g->objectId,
                classId: $g->classId,
                state: $this->resolvedGoogleObjectState(),
                ticketHolderName: $this->ticketHolderName,
                ticketNumber: $this->ticketNumber,
                barcode: $this->primaryGoogleBarcode(),
                hexBackgroundColor: $this->resolvedBackgroundColor(),
                validTimeInterval: $this->common->validTimeInterval,
                linksModuleData: $this->common->linksModuleData,
                appLinkData: $this->common->appLinkData,
                groupingInfo: $this->resolvedGoogleGrouping(),
            );

            $googlePair = new GoogleWalletPair(GoogleVerticalEnum::EVENT_TICKET, $eventClass, $eventObject);
        }

        $samsungCard = null;
        if ($this->context->hasSamsung()) {
            $s = $this->context->samsung;
            $now = (int) (microtime(true) * 1000);
            $attributes = new EventTicketAttributes(
                title: $this->eventName,
                providerName: $this->context->hasApple() ? $this->context->apple->organizationName : ($this->context->hasGoogle() ? $this->context->googleIssuerName() : ''),
                issueDate: $now,
                reservationNumber: $this->ticketNumber ?? '',
                startDate: $now,
                noticeDesc: $this->context->hasApple() ? $this->context->apple->description : '',
                appLinkLogo: $s->appLinkLogo ?? '',
                appLinkName: $s->appLinkName ?? '',
                appLinkData: $s->appLinkData ?? '',
                holderName: $this->ticketHolderName,
                seatNumber: null,
                barcode: $this->primarySamsungBarcode(),
                bgColor: $this->resolvedBackgroundColor(),
            );
            $samsungCard = $this->createSamsungCard(CardTypeEnum::TICKET, CardSubTypeEnum::PERFORMANCES, $attributes);
        }

        return new BuiltWalletPass($applePass, $googlePair, $samsungCard);
    }
}
