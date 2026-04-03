<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Generic;

/**
 * @phpstan-type UpcomingNotificationType array{enableNotification?: bool}
 */
class UpcomingNotification
{
    public function __construct(
        public ?bool $enableNotification = null,
    ) {
    }
}
