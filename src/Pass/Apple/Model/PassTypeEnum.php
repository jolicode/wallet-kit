<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

enum PassTypeEnum: string
{
    case BoardingPass = 'boardingPass';
    case Coupon = 'coupon';
    case EventTicket = 'eventTicket';
    case Generic = 'generic';
    case StoreCard = 'storeCard';
}
