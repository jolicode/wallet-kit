<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Model\Shared;

/**
 * @phpstan-type CardType 'boardingpass'|'ticket'|'coupon'|'giftcard'|'loyalty'|'idcard'|'payasyougo'|'generic'
 */
enum CardTypeEnum: string
{
    case BOARDING_PASS = 'boardingpass';
    case TICKET = 'ticket';
    case COUPON = 'coupon';
    case GIFT_CARD = 'giftcard';
    case LOYALTY = 'loyalty';
    case ID_CARD = 'idcard';
    case PAY_AS_YOU_GO = 'payasyougo';
    case GENERIC = 'generic';
}
