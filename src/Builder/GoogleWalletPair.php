<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder;

use Jolicode\WalletKit\Pass\Android\Model\EventTicket\EventTicketClass;
use Jolicode\WalletKit\Pass\Android\Model\EventTicket\EventTicketObject;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightClass;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightObject;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericClass;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericObject;
use Jolicode\WalletKit\Pass\Android\Model\GiftCard\GiftCardClass;
use Jolicode\WalletKit\Pass\Android\Model\GiftCard\GiftCardObject;
use Jolicode\WalletKit\Pass\Android\Model\Loyalty\LoyaltyClass;
use Jolicode\WalletKit\Pass\Android\Model\Loyalty\LoyaltyObject;
use Jolicode\WalletKit\Pass\Android\Model\Offer\OfferClass;
use Jolicode\WalletKit\Pass\Android\Model\Offer\OfferObject;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitClass;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitObject;

/**
 * @phpstan-type GoogleIssuerClass FlightClass|EventTicketClass|GenericClass|GiftCardClass|LoyaltyClass|OfferClass|TransitClass
 * @phpstan-type GooglePassObject FlightObject|EventTicketObject|GenericObject|GiftCardObject|LoyaltyObject|OfferObject|TransitObject
 */
final class GoogleWalletPair
{
    /**
     * @param GoogleIssuerClass $issuerClass
     * @param GooglePassObject  $passObject
     */
    public function __construct(
        public readonly GoogleVerticalEnum $vertical,
        public readonly FlightClass|EventTicketClass|GenericClass|GiftCardClass|LoyaltyClass|OfferClass|TransitClass $issuerClass,
        public readonly FlightObject|EventTicketObject|GenericObject|GiftCardObject|LoyaltyObject|OfferObject|TransitObject $passObject,
    ) {
    }
}
