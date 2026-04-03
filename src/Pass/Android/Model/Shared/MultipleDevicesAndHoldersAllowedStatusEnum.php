<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type MultipleDevicesAndHoldersAllowedStatus 'STATUS_UNSPECIFIED'|'MULTIPLE_HOLDERS'|'ONE_USER_ALL_DEVICES'|'ONE_USER_ONE_DEVICE'
 */
enum MultipleDevicesAndHoldersAllowedStatusEnum: string
{
    case UNSPECIFIED = 'STATUS_UNSPECIFIED';
    case MULTIPLE_HOLDERS = 'MULTIPLE_HOLDERS';
    case ONE_USER_ALL_DEVICES = 'ONE_USER_ALL_DEVICES';
    case ONE_USER_ONE_DEVICE = 'ONE_USER_ONE_DEVICE';
}
