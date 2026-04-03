<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Generic;

/**
 * @phpstan-import-type ExpiryNotificationType from ExpiryNotification
 * @phpstan-import-type UpcomingNotificationType from UpcomingNotification
 *
 * @phpstan-type NotificationsType array{expiryNotification?: ExpiryNotificationType, upcomingNotification?: UpcomingNotificationType}
 */
class Notifications
{
    public function __construct(
        public ?ExpiryNotification $expiryNotification = null,
        public ?UpcomingNotification $upcomingNotification = null,
    ) {
    }
}
