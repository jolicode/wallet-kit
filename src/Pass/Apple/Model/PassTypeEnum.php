<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

enum PassTypeEnum: string
{
    case BOARDING_PASS = 'boardingPass';
    case COUPON = 'coupon';
    case EVENT_TICKET = 'eventTicket';
    case GENERIC = 'generic';
    case STORE_CARD = 'storeCard';
}
