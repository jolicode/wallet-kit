<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Generic;

/**
 * @phpstan-type ExpiryNotificationType array{enableNotification?: bool}
 */
class ExpiryNotification
{
    public function __construct(
        public ?bool $enableNotification = null,
    ) {
    }
}
