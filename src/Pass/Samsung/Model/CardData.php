<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Model;

use Jolicode\WalletKit\Pass\Samsung\Model\BoardingPass\BoardingPassAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\Coupon\CouponAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\DigitalId\DigitalIdAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\EventTicket\EventTicketAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\Generic\GenericAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\GiftCard\GiftCardAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\Loyalty\LoyaltyAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\PayAsYouGo\PayAsYouGoAttributes;

/**
 * @phpstan-import-type LocalizationType from Localization
 *
 * @phpstan-type CardDataType array{refId: string, createdAt: int, updatedAt: int, language: string, attributes: array<string, mixed>, localization?: list<LocalizationType>}
 */
class CardData
{
    /**
     * @param list<Localization>|null $localization
     */
    public function __construct(
        public string $refId,
        public int $createdAt,
        public int $updatedAt,
        public string $language,
        public BoardingPassAttributes|EventTicketAttributes|CouponAttributes|GiftCardAttributes|LoyaltyAttributes|GenericAttributes|DigitalIdAttributes|PayAsYouGoAttributes $attributes,
        public ?array $localization = null,
    ) {
    }
}
