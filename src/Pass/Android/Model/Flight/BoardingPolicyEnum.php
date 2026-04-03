<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Flight;

/**
 * @phpstan-type BoardingPolicy 'BOARDING_POLICY_UNSPECIFIED'|'ZONE_BASED'|'GROUP_BASED'|'BOARDING_POLICY_OTHER'
 */
enum BoardingPolicyEnum: string
{
    case UNSPECIFIED = 'BOARDING_POLICY_UNSPECIFIED';
    case ZONE_BASED = 'ZONE_BASED';
    case GROUP_BASED = 'GROUP_BASED';
    case BOARDING_POLICY_OTHER = 'BOARDING_POLICY_OTHER';
}
