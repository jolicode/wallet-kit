<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Model\Shared;

/**
 * @phpstan-type CardSubType 'airlines'|'trains'|'buses'|'performances'|'sports'|'movies'|'entrances'|'employees'|'nationals'|'students'|'drivers'|'guests'|'evcharges'|'others'
 */
enum CardSubTypeEnum: string
{
    case AIRLINES = 'airlines';
    case TRAINS = 'trains';
    case BUSES = 'buses';
    case PERFORMANCES = 'performances';
    case SPORTS = 'sports';
    case MOVIES = 'movies';
    case ENTRANCES = 'entrances';
    case EMPLOYEES = 'employees';
    case NATIONALS = 'nationals';
    case STUDENTS = 'students';
    case DRIVERS = 'drivers';
    case GUESTS = 'guests';
    case EV_CHARGES = 'evcharges';
    case OTHERS = 'others';
}
