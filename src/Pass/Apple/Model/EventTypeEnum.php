<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-type EventType 'PKEventTypeGeneric'|'PKEventTypeLivePerformance'|'PKEventTypeMovie'|'PKEventTypeSports'|'PKEventTypeConference'|'PKEventTypeConvention'|'PKEventTypeWorkshop'|'PKEventTypeSocialGathering'
 */
enum EventTypeEnum: string
{
    case Generic = 'PKEventTypeGeneric';
    case LivePerformance = 'PKEventTypeLivePerformance';
    case Movie = 'PKEventTypeMovie';
    case Sports = 'PKEventTypeSports';
    case Conference = 'PKEventTypeConference';
    case Convention = 'PKEventTypeConvention';
    case Workshop = 'PKEventTypeWorkshop';
    case SocialGathering = 'PKEventTypeSocialGathering';
}
