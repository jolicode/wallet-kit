<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type NotifyPreference 'NOTIFICATION_SETTINGS_FOR_UPDATES_UNSPECIFIED'|'NOTIFY_ON_UPDATE'
 */
enum NotifyPreferenceEnum: string
{
    case NOTIFICATION_SETTINGS_FOR_UPDATES_UNSPECIFIED = 'NOTIFICATION_SETTINGS_FOR_UPDATES_UNSPECIFIED';
    case NOTIFY_ON_UPDATE = 'NOTIFY_ON_UPDATE';
}
