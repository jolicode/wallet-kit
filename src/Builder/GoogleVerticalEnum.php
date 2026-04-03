<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder;

enum GoogleVerticalEnum: string
{
    case FLIGHT = 'flight';
    case EVENT_TICKET = 'event_ticket';
    case GENERIC = 'generic';
    case GIFT_CARD = 'gift_card';
    case LOYALTY = 'loyalty';
    case OFFER = 'offer';
    case TRANSIT = 'transit';
}
