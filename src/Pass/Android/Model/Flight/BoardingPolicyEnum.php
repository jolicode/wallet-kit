<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Flight;

/**
 * @phpstan-type BoardingPolicy 'BOARDING_POLICY_UNSPECIFIED'|'ZONE_BASED'|'GROUP_BASED'|'BOARDING_POLICY_OTHER'
 */
enum BoardingPolicyEnum: string
{
    case Unspecified = 'BOARDING_POLICY_UNSPECIFIED';
    case ZoneBased = 'ZONE_BASED';
    case GroupBased = 'GROUP_BASED';
    case BoardingPolicyOther = 'BOARDING_POLICY_OTHER';
}
