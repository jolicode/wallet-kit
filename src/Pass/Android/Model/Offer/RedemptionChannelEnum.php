<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Offer;

/**
 * @phpstan-type RedemptionChannel 'REDEMPTION_CHANNEL_UNSPECIFIED'|'INSTORE'|'ONLINE'|'BOTH'|'TEMPORARY_PRICE_REDUCTION'
 */
enum RedemptionChannelEnum: string
{
    case Unspecified = 'REDEMPTION_CHANNEL_UNSPECIFIED';
    case Instore = 'INSTORE';
    case Online = 'ONLINE';
    case Both = 'BOTH';
    case TemporaryPriceReduction = 'TEMPORARY_PRICE_REDUCTION';
}
