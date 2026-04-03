<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder;

use Jolicode\WalletKit\Builder\EventTicket\EventTicketWalletBuilder;
use Jolicode\WalletKit\Builder\Flight\FlightWalletBuilder;
use Jolicode\WalletKit\Builder\Generic\GenericWalletBuilder;
use Jolicode\WalletKit\Builder\GiftCard\GiftCardWalletBuilder;
use Jolicode\WalletKit\Builder\Loyalty\LoyaltyWalletBuilder;
use Jolicode\WalletKit\Builder\Offer\OfferWalletBuilder;
use Jolicode\WalletKit\Builder\Transit\TransitWalletBuilder;
use Jolicode\WalletKit\Pass\Android\Model\Flight\AirportInfo;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightHeader;
use Jolicode\WalletKit\Pass\Android\Model\Flight\ReservationInfo;
use Jolicode\WalletKit\Pass\Android\Model\Offer\RedemptionChannelEnum;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TripTypeEnum;

/**
 * Entry point for fluent dual-platform wallet builders.
 */
final class WalletPass
{
    public static function generic(WalletPlatformContext $context): GenericWalletBuilder
    {
        return new GenericWalletBuilder($context);
    }

    public static function offer(
        WalletPlatformContext $context,
        string $title,
        string $provider,
        RedemptionChannelEnum $redemptionChannel,
    ): OfferWalletBuilder {
        return new OfferWalletBuilder($context, $title, $provider, $redemptionChannel);
    }

    public static function loyalty(WalletPlatformContext $context, ?string $programName = null): LoyaltyWalletBuilder
    {
        return new LoyaltyWalletBuilder($context, $programName);
    }

    public static function eventTicket(WalletPlatformContext $context, string $eventName): EventTicketWalletBuilder
    {
        return new EventTicketWalletBuilder($context, $eventName);
    }

    public static function flight(
        WalletPlatformContext $context,
        string $passengerName,
        ReservationInfo $reservationInfo,
        FlightHeader $flightHeader,
        AirportInfo $origin,
        AirportInfo $destination,
    ): FlightWalletBuilder {
        return new FlightWalletBuilder($context, $passengerName, $reservationInfo, $flightHeader, $origin, $destination);
    }

    public static function transit(
        WalletPlatformContext $context,
        TransitTypeEnum $transitType,
        TripTypeEnum $tripType,
    ): TransitWalletBuilder {
        return new TransitWalletBuilder($context, $transitType, $tripType);
    }

    public static function giftCard(WalletPlatformContext $context, string $cardNumber): GiftCardWalletBuilder
    {
        return new GiftCardWalletBuilder($context, $cardNumber);
    }
}
