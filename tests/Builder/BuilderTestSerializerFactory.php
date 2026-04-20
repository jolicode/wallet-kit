<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Builder;

use Jolicode\WalletKit\Pass\Android\Normalizer\EventTicket\EventReservationInfoNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\EventTicket\EventSeatNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\EventTicket\EventTicketClassNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\EventTicket\EventTicketObjectNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Flight\AirportInfoNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Flight\BoardingAndSeatingInfoNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Flight\FlightCarrierNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Flight\FlightClassNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Flight\FlightHeaderNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Flight\FlightObjectNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Flight\FrequentFlyerInfoNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Flight\ReservationInfoNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Generic\ExpiryNotificationNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Generic\GenericClassNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Generic\GenericObjectNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Generic\NotificationsNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Generic\UpcomingNotificationNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\GiftCard\GiftCardClassNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\GiftCard\GiftCardObjectNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Loyalty\LoyaltyClassNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Loyalty\LoyaltyObjectNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Loyalty\LoyaltyPointsBalanceNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Loyalty\LoyaltyPointsNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Offer\OfferClassNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Offer\OfferObjectNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\AppLinkDataNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\AppLinkInfoNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\AppTargetNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\BarcodeNormalizer as GoogleBarcodeNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\CallbackOptionsNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\GoogleDateTimeNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\GroupingInfoNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\ImageModuleDataNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\ImageNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\ImageUriNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\LatLongPointNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\LinksModuleDataNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\LocalizedStringNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\MessageNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\MoneyNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\PassConstraintsNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\RotatingBarcodeNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\SecurityAnimationNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\TextModuleDataNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\TimeIntervalNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\TranslatedStringNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\UriNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Transit\ActivationStatusNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Transit\PurchaseDetailsNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Transit\TicketCostNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Transit\TicketLegNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Transit\TicketRestrictionsNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Transit\TicketSeatNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Transit\TransitClassNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Transit\TransitObjectNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\BarcodeNormalizer as AppleBarcodeNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\BeaconNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\FieldNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\LocationNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\NfcNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\PassNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\PassStructureNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\RelevantDateNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagsNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType\CurrencyAmountNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType\EventDateInfoNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType\PersonNameComponentsNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType\SeatNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType\SemanticLocationNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType\WifiNetworkNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\BoardingPass\BoardingPassAttributesNormalizer as SamsungBoardingPassAttributesNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\CardDataNormalizer as SamsungCardDataNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\CardNormalizer as SamsungCardNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\Coupon\CouponAttributesNormalizer as SamsungCouponAttributesNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\DigitalId\DigitalIdAttributesNormalizer as SamsungDigitalIdAttributesNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\EventTicket\EventTicketAttributesNormalizer as SamsungEventTicketAttributesNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\Generic\GenericAttributesNormalizer as SamsungGenericAttributesNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\GiftCard\GiftCardAttributesNormalizer as SamsungGiftCardAttributesNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\LocalizationNormalizer as SamsungLocalizationNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\Loyalty\LoyaltyAttributesNormalizer as SamsungLoyaltyAttributesNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\PayAsYouGo\PayAsYouGoAttributesNormalizer as SamsungPayAsYouGoAttributesNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\Shared\LocationNormalizer as SamsungLocationNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\Shared\SamsungBarcodeNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\Shared\SamsungImageNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

final class BuilderTestSerializerFactory
{
    public static function create(): Serializer
    {
        return new Serializer([
            new PassNormalizer(),
            new PassStructureNormalizer(),
            new FieldNormalizer(),
            new AppleBarcodeNormalizer(),
            new NfcNormalizer(),
            new LocationNormalizer(),
            new BeaconNormalizer(),
            new RelevantDateNormalizer(),
            new SemanticTagsNormalizer(),
            new SeatNormalizer(),
            new PersonNameComponentsNormalizer(),
            new CurrencyAmountNormalizer(),
            new SemanticLocationNormalizer(),
            new EventDateInfoNormalizer(),
            new WifiNetworkNormalizer(),
            new TranslatedStringNormalizer(),
            new LocalizedStringNormalizer(),
            new ImageUriNormalizer(),
            new ImageNormalizer(),
            new GoogleBarcodeNormalizer(),
            new RotatingBarcodeNormalizer(),
            new MoneyNormalizer(),
            new GoogleDateTimeNormalizer(),
            new TimeIntervalNormalizer(),
            new MessageNormalizer(),
            new LatLongPointNormalizer(),
            new TextModuleDataNormalizer(),
            new UriNormalizer(),
            new LinksModuleDataNormalizer(),
            new ImageModuleDataNormalizer(),
            new GroupingInfoNormalizer(),
            new PassConstraintsNormalizer(),
            new CallbackOptionsNormalizer(),
            new SecurityAnimationNormalizer(),
            new AppTargetNormalizer(),
            new AppLinkInfoNormalizer(),
            new AppLinkDataNormalizer(),
            new ExpiryNotificationNormalizer(),
            new UpcomingNotificationNormalizer(),
            new NotificationsNormalizer(),
            new GenericClassNormalizer(),
            new GenericObjectNormalizer(),
            new LoyaltyPointsBalanceNormalizer(),
            new LoyaltyPointsNormalizer(),
            new LoyaltyClassNormalizer(),
            new LoyaltyObjectNormalizer(),
            new OfferClassNormalizer(),
            new OfferObjectNormalizer(),
            new GiftCardClassNormalizer(),
            new GiftCardObjectNormalizer(),
            new EventSeatNormalizer(),
            new EventReservationInfoNormalizer(),
            new EventTicketClassNormalizer(),
            new EventTicketObjectNormalizer(),
            new AirportInfoNormalizer(),
            new FlightCarrierNormalizer(),
            new FlightHeaderNormalizer(),
            new FrequentFlyerInfoNormalizer(),
            new ReservationInfoNormalizer(),
            new BoardingAndSeatingInfoNormalizer(),
            new FlightClassNormalizer(),
            new FlightObjectNormalizer(),
            new TicketSeatNormalizer(),
            new TicketLegNormalizer(),
            new TicketRestrictionsNormalizer(),
            new TicketCostNormalizer(),
            new PurchaseDetailsNormalizer(),
            new ActivationStatusNormalizer(),
            new TransitClassNormalizer(),
            new TransitObjectNormalizer(),
            new SamsungImageNormalizer(),
            new SamsungBarcodeNormalizer(),
            new SamsungLocationNormalizer(),
            new SamsungBoardingPassAttributesNormalizer(),
            new SamsungEventTicketAttributesNormalizer(),
            new SamsungCouponAttributesNormalizer(),
            new SamsungGiftCardAttributesNormalizer(),
            new SamsungLoyaltyAttributesNormalizer(),
            new SamsungGenericAttributesNormalizer(),
            new SamsungDigitalIdAttributesNormalizer(),
            new SamsungPayAsYouGoAttributesNormalizer(),
            new SamsungLocalizationNormalizer(),
            new SamsungCardDataNormalizer(),
            new SamsungCardNormalizer(),
        ], [
            new JsonEncoder(),
        ]);
    }
}
