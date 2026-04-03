<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\EventTicket;

/**
 * @phpstan-type EventReservationInfoType array{confirmationCode?: string}
 */
class EventReservationInfo
{
    public function __construct(
        public ?string $confirmationCode = null,
    ) {
    }
}
