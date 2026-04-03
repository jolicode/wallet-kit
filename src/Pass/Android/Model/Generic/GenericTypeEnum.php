<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Generic;

/**
 * @phpstan-type GenericType 'GENERIC_TYPE_UNSPECIFIED'|'GENERIC_SEASON_PASS'|'GENERIC_UTILITY_BILLS'|'GENERIC_PARKING_PASS'|'GENERIC_VOUCHER'|'GENERIC_GYM_MEMBERSHIP'|'GENERIC_LIBRARY_MEMBERSHIP'|'GENERIC_RESERVATIONS'|'GENERIC_AUTO_INSURANCE'|'GENERIC_HOME_INSURANCE'|'GENERIC_ENTRY_TICKET'|'GENERIC_RECEIPT'|'GENERIC_LOYALTY_CARD'|'GENERIC_OTHER'
 */
enum GenericTypeEnum: string
{
    case Unspecified = 'GENERIC_TYPE_UNSPECIFIED';
    case SeasonPass = 'GENERIC_SEASON_PASS';
    case UtilityBills = 'GENERIC_UTILITY_BILLS';
    case ParkingPass = 'GENERIC_PARKING_PASS';
    case Voucher = 'GENERIC_VOUCHER';
    case GymMembership = 'GENERIC_GYM_MEMBERSHIP';
    case LibraryMembership = 'GENERIC_LIBRARY_MEMBERSHIP';
    case Reservations = 'GENERIC_RESERVATIONS';
    case AutoInsurance = 'GENERIC_AUTO_INSURANCE';
    case HomeInsurance = 'GENERIC_HOME_INSURANCE';
    case EntryTicket = 'GENERIC_ENTRY_TICKET';
    case Receipt = 'GENERIC_RECEIPT';
    case LoyaltyCard = 'GENERIC_LOYALTY_CARD';
    case Other = 'GENERIC_OTHER';
}
