<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Offer;

/**
 * @phpstan-type RedemptionChannel 'REDEMPTION_CHANNEL_UNSPECIFIED'|'INSTORE'|'ONLINE'|'BOTH'|'TEMPORARY_PRICE_REDUCTION'
 */
enum RedemptionChannelEnum: string
{
    case UNSPECIFIED = 'REDEMPTION_CHANNEL_UNSPECIFIED';
    case INSTORE = 'INSTORE';
    case ONLINE = 'ONLINE';
    case BOTH = 'BOTH';
    case TEMPORARY_PRICE_REDUCTION = 'TEMPORARY_PRICE_REDUCTION';
}
