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
 * @phpstan-type LocalizationType array{language: string, attributes: array<string, mixed>}
 */
class Localization
{
    public function __construct(
        public string $language,
        public BoardingPassAttributes|EventTicketAttributes|CouponAttributes|GiftCardAttributes|LoyaltyAttributes|GenericAttributes|DigitalIdAttributes|PayAsYouGoAttributes $attributes,
    ) {
    }
}
