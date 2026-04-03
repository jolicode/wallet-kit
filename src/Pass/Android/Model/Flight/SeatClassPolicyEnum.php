<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Flight;

/**
 * @phpstan-type SeatClassPolicy 'SEAT_CLASS_POLICY_UNSPECIFIED'|'CABIN_BASED'|'CLASS_BASED'|'TIER_BASED'|'SEAT_CLASS_POLICY_OTHER'
 */
enum SeatClassPolicyEnum: string
{
    case Unspecified = 'SEAT_CLASS_POLICY_UNSPECIFIED';
    case CabinBased = 'CABIN_BASED';
    case ClassBased = 'CLASS_BASED';
    case TierBased = 'TIER_BASED';
    case SeatClassPolicyOther = 'SEAT_CLASS_POLICY_OTHER';
}
