<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Flight;

/**
 * @phpstan-type SeatClassPolicy 'SEAT_CLASS_POLICY_UNSPECIFIED'|'CABIN_BASED'|'CLASS_BASED'|'TIER_BASED'|'SEAT_CLASS_POLICY_OTHER'
 */
enum SeatClassPolicyEnum: string
{
    case UNSPECIFIED = 'SEAT_CLASS_POLICY_UNSPECIFIED';
    case CABIN_BASED = 'CABIN_BASED';
    case CLASS_BASED = 'CLASS_BASED';
    case TIER_BASED = 'TIER_BASED';
    case SEAT_CLASS_POLICY_OTHER = 'SEAT_CLASS_POLICY_OTHER';
}
