<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

/**
 * @phpstan-type TicketStatus 'TICKET_STATUS_UNSPECIFIED'|'USED'|'REFUNDED'|'EXCHANGED'
 */
enum TicketStatusEnum: string
{
    case Unspecified = 'TICKET_STATUS_UNSPECIFIED';
    case Used = 'USED';
    case Refunded = 'REFUNDED';
    case Exchanged = 'EXCHANGED';
}
