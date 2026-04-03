<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

/**
 * @phpstan-type TicketStatus 'TICKET_STATUS_UNSPECIFIED'|'USED'|'REFUNDED'|'EXCHANGED'
 */
enum TicketStatusEnum: string
{
    case UNSPECIFIED = 'TICKET_STATUS_UNSPECIFIED';
    case USED = 'USED';
    case REFUNDED = 'REFUNDED';
    case EXCHANGED = 'EXCHANGED';
}
