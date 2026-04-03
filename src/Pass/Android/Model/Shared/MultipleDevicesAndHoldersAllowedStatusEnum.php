<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type MultipleDevicesAndHoldersAllowedStatus 'STATUS_UNSPECIFIED'|'MULTIPLE_HOLDERS'|'ONE_USER_ALL_DEVICES'|'ONE_USER_ONE_DEVICE'
 */
enum MultipleDevicesAndHoldersAllowedStatusEnum: string
{
    case Unspecified = 'STATUS_UNSPECIFIED';
    case MultipleHolders = 'MULTIPLE_HOLDERS';
    case OneUserAllDevices = 'ONE_USER_ALL_DEVICES';
    case OneUserOneDevice = 'ONE_USER_ONE_DEVICE';
}
