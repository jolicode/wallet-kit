<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Model;

use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardSubTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardTypeEnum;

/**
 * @phpstan-import-type CardType from CardTypeEnum
 * @phpstan-import-type CardSubType from CardSubTypeEnum
 * @phpstan-import-type CardDataType from CardData
 *
 * @phpstan-type CardEnvelopeType array{card: array{type: CardType, subType: CardSubType, data: list<CardDataType>}}
 */
class Card
{
    /**
     * @param list<CardData> $data
     */
    public function __construct(
        public CardTypeEnum $type,
        public CardSubTypeEnum $subType,
        public array $data,
    ) {
    }
}
