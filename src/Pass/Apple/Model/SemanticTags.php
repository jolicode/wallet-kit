<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\CurrencyAmount;
use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\EventDateInfo;
use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\PersonNameComponents;
use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\Seat;
use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\SemanticLocation;
use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\WifiNetwork;

/**
 * @phpstan-import-type SeatType from Seat
 * @phpstan-import-type PersonNameComponentsType from PersonNameComponents
 * @phpstan-import-type CurrencyAmountType from CurrencyAmount
 * @phpstan-import-type SemanticLocationType from SemanticLocation
 * @phpstan-import-type EventDateInfoType from EventDateInfo
 * @phpstan-import-type WifiNetworkType from WifiNetwork
 * @phpstan-import-type EventType from EventTypeEnum
 *
 * @phpstan-type SemanticTagsType array{
 *     eventName?: string,
 *     eventType?: EventType,
 *     eventStartDate?: string,
 *     eventEndDate?: string,
 *     eventStartDateInfo?: EventDateInfoType,
 *     venueName?: string,
 *     venueRegionName?: string,
 *     venueRoom?: string,
 *     venueLocation?: SemanticLocationType,
 *     admissionLevel?: string,
 *     attendeeName?: string,
 *     performerNames?: list<string>,
 *     artistIDs?: list<string>,
 *     seats?: list<SeatType>,
 *     totalPrice?: CurrencyAmountType,
 *     awayTeamAbbreviation?: string,
 *     awayTeamName?: string,
 *     homeTeamAbbreviation?: string,
 *     homeTeamLocation?: string,
 *     homeTeamName?: string,
 *     leagueAbbreviation?: string,
 *     leagueName?: string,
 *     sportName?: string,
 *     airlineCode?: string,
 *     flightNumber?: int,
 *     departureAirportCode?: string,
 *     departureAirportName?: string,
 *     departureCityName?: string,
 *     departureLocation?: SemanticLocationType,
 *     departureLocationTimeZone?: string,
 *     departureGate?: string,
 *     departureTerminal?: string,
 *     destinationAirportCode?: string,
 *     destinationAirportName?: string,
 *     destinationCityName?: string,
 *     destinationLocation?: SemanticLocationType,
 *     destinationLocationTimeZone?: string,
 *     destinationGate?: string,
 *     destinationTerminal?: string,
 *     originalArrivalDate?: string,
 *     originalBoardingDate?: string,
 *     originalDepartureDate?: string,
 *     currentArrivalDate?: string,
 *     currentBoardingDate?: string,
 *     currentDepartureDate?: string,
 *     passengerName?: PersonNameComponentsType,
 *     boardingGroup?: string,
 *     boardingSequenceNumber?: string,
 *     boardingZone?: string,
 *     transitProvider?: string,
 *     membershipProgramName?: string,
 *     membershipProgramNumber?: string,
 *     membershipProgramStatus?: string,
 *     priorityStatus?: string,
 *     ticketFareClass?: string,
 *     departureLocationSecurityPrograms?: list<string>,
 *     destinationLocationSecurityPrograms?: list<string>,
 *     passengerEligibleSecurityPrograms?: list<string>,
 *     passengerAirlineSSRs?: list<string>,
 *     passengerCapabilities?: list<string>,
 *     passengerInformationSSRs?: list<string>,
 *     passengerServiceSSRs?: list<string>,
 *     internationalDocumentsAreVerified?: bool,
 *     internationalDocumentsVerifiedDeclarationName?: string,
 *     purchaseAdditionalBaggageURL?: string,
 *     transitProviderEmail?: string,
 *     transitProviderPhoneNumber?: string,
 *     transitProviderWebsiteURL?: string,
 *     businessChatIdentifier?: string,
 *     changeSeatURL?: string,
 *     entertainmentURL?: string,
 *     purchaseWifiURL?: string,
 *     managementURL?: string,
 *     orderFoodURL?: string,
 *     purchaseLoungeAccessURL?: string,
 *     reportLostBagURL?: string,
 *     trackBagsURL?: string,
 *     upgradeURL?: string,
 *     loungePlaceIDs?: list<string>,
 *     balance?: CurrencyAmountType,
 *     wifiAccess?: list<WifiNetworkType>,
 * }
 */
class SemanticTags
{
    /**
     * @param Seat[]|null        $seats
     * @param string[]|null      $performerNames
     * @param string[]|null      $artistIDs
     * @param string[]|null      $departureLocationSecurityPrograms
     * @param string[]|null      $destinationLocationSecurityPrograms
     * @param string[]|null      $passengerEligibleSecurityPrograms
     * @param string[]|null      $passengerAirlineSSRs
     * @param string[]|null      $passengerCapabilities
     * @param string[]|null      $passengerInformationSSRs
     * @param string[]|null      $passengerServiceSSRs
     * @param string[]|null      $loungePlaceIDs
     * @param WifiNetwork[]|null $wifiAccess
     */
    public function __construct(
        // -- General / Event --
        public ?string $eventName = null,
        public ?EventTypeEnum $eventType = null,
        public ?string $eventStartDate = null,
        public ?string $eventEndDate = null,
        public ?EventDateInfo $eventStartDateInfo = null,
        public ?string $venueName = null,
        public ?string $venueRegionName = null,
        public ?string $venueRoom = null,
        public ?SemanticLocation $venueLocation = null,
        public ?string $admissionLevel = null,
        public ?string $attendeeName = null,
        public ?array $performerNames = null,
        public ?array $artistIDs = null,
        public ?array $seats = null,
        public ?CurrencyAmount $totalPrice = null,

        // -- Sport events --
        public ?string $awayTeamAbbreviation = null,
        public ?string $awayTeamName = null,
        public ?string $homeTeamAbbreviation = null,
        public ?string $homeTeamLocation = null,
        public ?string $homeTeamName = null,
        public ?string $leagueAbbreviation = null,
        public ?string $leagueName = null,
        public ?string $sportName = null,

        // -- Boarding pass: flight identification --
        public ?string $airlineCode = null,
        public ?int $flightNumber = null,
        public ?string $departureAirportCode = null,
        public ?string $departureAirportName = null,
        public ?string $departureCityName = null,
        public ?SemanticLocation $departureLocation = null,
        public ?string $departureLocationTimeZone = null,
        public ?string $departureGate = null,
        public ?string $departureTerminal = null,
        public ?string $destinationAirportCode = null,
        public ?string $destinationAirportName = null,
        public ?string $destinationCityName = null,
        public ?SemanticLocation $destinationLocation = null,
        public ?string $destinationLocationTimeZone = null,
        public ?string $destinationGate = null,
        public ?string $destinationTerminal = null,

        // -- Boarding pass: dates --
        public ?string $originalArrivalDate = null,
        public ?string $originalBoardingDate = null,
        public ?string $originalDepartureDate = null,
        public ?string $currentArrivalDate = null,
        public ?string $currentBoardingDate = null,
        public ?string $currentDepartureDate = null,

        // -- Boarding pass: passenger --
        public ?PersonNameComponents $passengerName = null,
        public ?string $boardingGroup = null,
        public ?string $boardingSequenceNumber = null,
        public ?string $boardingZone = null,
        public ?string $transitProvider = null,
        public ?string $membershipProgramName = null,
        public ?string $membershipProgramNumber = null,
        public ?string $membershipProgramStatus = null,
        public ?string $priorityStatus = null,
        public ?string $ticketFareClass = null,

        // -- Boarding pass: security / SSR --
        public ?array $departureLocationSecurityPrograms = null,
        public ?array $destinationLocationSecurityPrograms = null,
        public ?array $passengerEligibleSecurityPrograms = null,
        public ?array $passengerAirlineSSRs = null,
        public ?array $passengerCapabilities = null,
        public ?array $passengerInformationSSRs = null,
        public ?array $passengerServiceSSRs = null,
        public ?bool $internationalDocumentsAreVerified = null,
        public ?string $internationalDocumentsVerifiedDeclarationName = null,

        // -- Boarding pass: URLs / services --
        public ?string $purchaseAdditionalBaggageURL = null,
        public ?string $transitProviderEmail = null,
        public ?string $transitProviderPhoneNumber = null,
        public ?string $transitProviderWebsiteURL = null,
        public ?string $businessChatIdentifier = null,
        public ?string $changeSeatURL = null,
        public ?string $entertainmentURL = null,
        public ?string $purchaseWifiURL = null,
        public ?string $managementURL = null,
        public ?string $orderFoodURL = null,
        public ?string $purchaseLoungeAccessURL = null,
        public ?string $reportLostBagURL = null,
        public ?string $trackBagsURL = null,
        public ?string $upgradeURL = null,
        public ?array $loungePlaceIDs = null,

        // -- Store card / Coupon --
        public ?CurrencyAmount $balance = null,
        public ?array $wifiAccess = null,
    ) {
    }
}
