<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type MoneyType from Money
 * @phpstan-import-type ValueAddedFieldType from ValueAddedField
 *
 * @phpstan-type ValueAddedModuleDataType array{id?: string, totalValue?: MoneyType, fields?: list<ValueAddedFieldType>}
 */
class ValueAddedModuleData
{
    /**
     * @param list<ValueAddedField>|null $fields
     */
    public function __construct(
        public ?string $id = null,
        public ?Money $totalValue = null,
        public ?array $fields = null,
    ) {
    }
}
