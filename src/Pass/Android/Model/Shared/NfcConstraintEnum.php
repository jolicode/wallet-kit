<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type NfcConstraint 'NFC_CONSTRAINT_UNSPECIFIED'|'BLOCK_PAYMENT'|'BLOCK_CLOSED_LOOP_TRANSIT'
 */
enum NfcConstraintEnum: string
{
    case UNSPECIFIED = 'NFC_CONSTRAINT_UNSPECIFIED';
    case BLOCK_PAYMENT = 'BLOCK_PAYMENT';
    case BLOCK_CLOSED_LOOP_TRANSIT = 'BLOCK_CLOSED_LOOP_TRANSIT';
}
