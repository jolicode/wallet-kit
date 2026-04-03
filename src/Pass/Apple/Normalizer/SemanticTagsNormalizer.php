<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Normalizer;

use Jolicode\WalletKit\Pass\Apple\Model\SemanticTags;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type SemanticTagsType from SemanticTags
 */
class SemanticTagsNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param SemanticTags         $object
     * @param array<string, mixed> $context
     *
     * @return SemanticTagsType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        // -- General / Event --
        $this->setIfNotNull($data, 'eventName', $object->eventName);
        if (null !== $object->eventType) {
            $data['eventType'] = $object->eventType->value;
        }
        $this->setIfNotNull($data, 'eventStartDate', $object->eventStartDate);
        $this->setIfNotNull($data, 'eventEndDate', $object->eventEndDate);
        if (null !== $object->eventStartDateInfo) {
            $data['eventStartDateInfo'] = $this->normalizer->normalize($object->eventStartDateInfo, $format, $context);
        }
        $this->setIfNotNull($data, 'venueName', $object->venueName);
        $this->setIfNotNull($data, 'venueRegionName', $object->venueRegionName);
        $this->setIfNotNull($data, 'venueRoom', $object->venueRoom);
        if (null !== $object->venueLocation) {
            $data['venueLocation'] = $this->normalizer->normalize($object->venueLocation, $format, $context);
        }
        $this->setIfNotNull($data, 'admissionLevel', $object->admissionLevel);
        $this->setIfNotNull($data, 'attendeeName', $object->attendeeName);
        $this->setIfNotNull($data, 'performerNames', $object->performerNames);
        $this->setIfNotNull($data, 'artistIDs', $object->artistIDs);
        if (null !== $object->seats) {
            $seats = [];
            foreach ($object->seats as $seat) {
                $seats[] = $this->normalizer->normalize($seat, $format, $context);
            }
            $data['seats'] = $seats;
        }
        if (null !== $object->totalPrice) {
            $data['totalPrice'] = $this->normalizer->normalize($object->totalPrice, $format, $context);
        }

        // -- Sport events --
        $this->setIfNotNull($data, 'awayTeamAbbreviation', $object->awayTeamAbbreviation);
        $this->setIfNotNull($data, 'awayTeamName', $object->awayTeamName);
        $this->setIfNotNull($data, 'homeTeamAbbreviation', $object->homeTeamAbbreviation);
        $this->setIfNotNull($data, 'homeTeamLocation', $object->homeTeamLocation);
        $this->setIfNotNull($data, 'homeTeamName', $object->homeTeamName);
        $this->setIfNotNull($data, 'leagueAbbreviation', $object->leagueAbbreviation);
        $this->setIfNotNull($data, 'leagueName', $object->leagueName);
        $this->setIfNotNull($data, 'sportName', $object->sportName);

        // -- Boarding pass: flight identification --
        $this->setIfNotNull($data, 'airlineCode', $object->airlineCode);
        $this->setIfNotNull($data, 'flightNumber', $object->flightNumber);
        $this->setIfNotNull($data, 'departureAirportCode', $object->departureAirportCode);
        $this->setIfNotNull($data, 'departureAirportName', $object->departureAirportName);
        $this->setIfNotNull($data, 'departureCityName', $object->departureCityName);
        if (null !== $object->departureLocation) {
            $data['departureLocation'] = $this->normalizer->normalize($object->departureLocation, $format, $context);
        }
        $this->setIfNotNull($data, 'departureLocationTimeZone', $object->departureLocationTimeZone);
        $this->setIfNotNull($data, 'departureGate', $object->departureGate);
        $this->setIfNotNull($data, 'departureTerminal', $object->departureTerminal);
        $this->setIfNotNull($data, 'destinationAirportCode', $object->destinationAirportCode);
        $this->setIfNotNull($data, 'destinationAirportName', $object->destinationAirportName);
        $this->setIfNotNull($data, 'destinationCityName', $object->destinationCityName);
        if (null !== $object->destinationLocation) {
            $data['destinationLocation'] = $this->normalizer->normalize($object->destinationLocation, $format, $context);
        }
        $this->setIfNotNull($data, 'destinationLocationTimeZone', $object->destinationLocationTimeZone);
        $this->setIfNotNull($data, 'destinationGate', $object->destinationGate);
        $this->setIfNotNull($data, 'destinationTerminal', $object->destinationTerminal);

        // -- Boarding pass: dates --
        $this->setIfNotNull($data, 'originalArrivalDate', $object->originalArrivalDate);
        $this->setIfNotNull($data, 'originalBoardingDate', $object->originalBoardingDate);
        $this->setIfNotNull($data, 'originalDepartureDate', $object->originalDepartureDate);
        $this->setIfNotNull($data, 'currentArrivalDate', $object->currentArrivalDate);
        $this->setIfNotNull($data, 'currentBoardingDate', $object->currentBoardingDate);
        $this->setIfNotNull($data, 'currentDepartureDate', $object->currentDepartureDate);

        // -- Boarding pass: passenger --
        if (null !== $object->passengerName) {
            $data['passengerName'] = $this->normalizer->normalize($object->passengerName, $format, $context);
        }
        $this->setIfNotNull($data, 'boardingGroup', $object->boardingGroup);
        $this->setIfNotNull($data, 'boardingSequenceNumber', $object->boardingSequenceNumber);
        $this->setIfNotNull($data, 'boardingZone', $object->boardingZone);
        $this->setIfNotNull($data, 'transitProvider', $object->transitProvider);
        $this->setIfNotNull($data, 'membershipProgramName', $object->membershipProgramName);
        $this->setIfNotNull($data, 'membershipProgramNumber', $object->membershipProgramNumber);
        $this->setIfNotNull($data, 'membershipProgramStatus', $object->membershipProgramStatus);
        $this->setIfNotNull($data, 'priorityStatus', $object->priorityStatus);
        $this->setIfNotNull($data, 'ticketFareClass', $object->ticketFareClass);

        // -- Boarding pass: security / SSR --
        $this->setIfNotNull($data, 'departureLocationSecurityPrograms', $object->departureLocationSecurityPrograms);
        $this->setIfNotNull($data, 'destinationLocationSecurityPrograms', $object->destinationLocationSecurityPrograms);
        $this->setIfNotNull($data, 'passengerEligibleSecurityPrograms', $object->passengerEligibleSecurityPrograms);
        $this->setIfNotNull($data, 'passengerAirlineSSRs', $object->passengerAirlineSSRs);
        $this->setIfNotNull($data, 'passengerCapabilities', $object->passengerCapabilities);
        $this->setIfNotNull($data, 'passengerInformationSSRs', $object->passengerInformationSSRs);
        $this->setIfNotNull($data, 'passengerServiceSSRs', $object->passengerServiceSSRs);
        $this->setIfNotNull($data, 'internationalDocumentsAreVerified', $object->internationalDocumentsAreVerified);
        $this->setIfNotNull($data, 'internationalDocumentsVerifiedDeclarationName', $object->internationalDocumentsVerifiedDeclarationName);

        // -- Boarding pass: URLs / services --
        $this->setIfNotNull($data, 'purchaseAdditionalBaggageURL', $object->purchaseAdditionalBaggageURL);
        $this->setIfNotNull($data, 'transitProviderEmail', $object->transitProviderEmail);
        $this->setIfNotNull($data, 'transitProviderPhoneNumber', $object->transitProviderPhoneNumber);
        $this->setIfNotNull($data, 'transitProviderWebsiteURL', $object->transitProviderWebsiteURL);
        $this->setIfNotNull($data, 'businessChatIdentifier', $object->businessChatIdentifier);
        $this->setIfNotNull($data, 'changeSeatURL', $object->changeSeatURL);
        $this->setIfNotNull($data, 'entertainmentURL', $object->entertainmentURL);
        $this->setIfNotNull($data, 'purchaseWifiURL', $object->purchaseWifiURL);
        $this->setIfNotNull($data, 'managementURL', $object->managementURL);
        $this->setIfNotNull($data, 'orderFoodURL', $object->orderFoodURL);
        $this->setIfNotNull($data, 'purchaseLoungeAccessURL', $object->purchaseLoungeAccessURL);
        $this->setIfNotNull($data, 'reportLostBagURL', $object->reportLostBagURL);
        $this->setIfNotNull($data, 'trackBagsURL', $object->trackBagsURL);
        $this->setIfNotNull($data, 'upgradeURL', $object->upgradeURL);
        $this->setIfNotNull($data, 'loungePlaceIDs', $object->loungePlaceIDs);

        // -- Store card / Coupon --
        if (null !== $object->balance) {
            $data['balance'] = $this->normalizer->normalize($object->balance, $format, $context);
        }
        if (null !== $object->wifiAccess) {
            $wifiAccess = [];
            foreach ($object->wifiAccess as $wifi) {
                $wifiAccess[] = $this->normalizer->normalize($wifi, $format, $context);
            }
            $data['wifiAccess'] = $wifiAccess;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setIfNotNull(array &$data, string $key, mixed $value): void
    {
        if (null !== $value) {
            $data[$key] = $value;
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof SemanticTags;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [SemanticTags::class => true];
    }
}
