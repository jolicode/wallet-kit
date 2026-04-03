<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-type EventType 'PKEventTypeGeneric'|'PKEventTypeLivePerformance'|'PKEventTypeMovie'|'PKEventTypeSports'|'PKEventTypeConference'|'PKEventTypeConvention'|'PKEventTypeWorkshop'|'PKEventTypeSocialGathering'
 */
enum EventTypeEnum: string
{
    case GENERIC = 'PKEventTypeGeneric';
    case LIVE_PERFORMANCE = 'PKEventTypeLivePerformance';
    case MOVIE = 'PKEventTypeMovie';
    case SPORTS = 'PKEventTypeSports';
    case CONFERENCE = 'PKEventTypeConference';
    case CONVENTION = 'PKEventTypeConvention';
    case WORKSHOP = 'PKEventTypeWorkshop';
    case SOCIAL_GATHERING = 'PKEventTypeSocialGathering';
}
