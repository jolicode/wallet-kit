<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

/**
 * @phpstan-import-type TicketCostType from TicketCost
 *
 * @phpstan-type PurchaseDetailsType array{purchaseReceiptNumber?: string, purchaseDateTime?: string, accountId?: string, confirmationCode?: string, ticketCost?: TicketCostType}
 */
class PurchaseDetails
{
    public function __construct(
        public ?string $purchaseReceiptNumber = null,
        public ?string $purchaseDateTime = null,
        public ?string $accountId = null,
        public ?string $confirmationCode = null,
        public ?TicketCost $ticketCost = null,
    ) {
    }
}
