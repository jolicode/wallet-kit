<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type NfcConstraint 'NFC_CONSTRAINT_UNSPECIFIED'|'BLOCK_PAYMENT'|'BLOCK_CLOSED_LOOP_TRANSIT'
 */
enum NfcConstraintEnum: string
{
    case Unspecified = 'NFC_CONSTRAINT_UNSPECIFIED';
    case BlockPayment = 'BLOCK_PAYMENT';
    case BlockClosedLoopTransit = 'BLOCK_CLOSED_LOOP_TRANSIT';
}
