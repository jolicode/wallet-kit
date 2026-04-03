<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\EventTicket;

use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;

/**
 * @phpstan-import-type LocalizedStringType from LocalizedString
 *
 * @phpstan-type EventSeatType array{seat?: LocalizedStringType, row?: LocalizedStringType, section?: LocalizedStringType, gate?: LocalizedStringType}
 */
class EventSeat
{
    public function __construct(
        public ?LocalizedString $seat = null,
        public ?LocalizedString $row = null,
        public ?LocalizedString $section = null,
        public ?LocalizedString $gate = null,
    ) {
    }
}
