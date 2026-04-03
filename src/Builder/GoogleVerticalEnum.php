<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder;

enum GoogleVerticalEnum: string
{
    case Flight = 'flight';
    case EventTicket = 'event_ticket';
    case Generic = 'generic';
    case GiftCard = 'gift_card';
    case Loyalty = 'loyalty';
    case Offer = 'offer';
    case Transit = 'transit';
}
