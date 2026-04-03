<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-import-type FieldType from Field
 * @phpstan-import-type TransitType from TransitTypeEnum
 *
 * @phpstan-type PassStructureType array{
 *     transitType?: TransitType,
 *     headerFields?: list<FieldType>,
 *     primaryFields?: list<FieldType>,
 *     secondaryFields?: list<FieldType>,
 *     auxiliaryFields?: list<FieldType>,
 *     backFields?: list<FieldType>,
 *     additionalInfoFields?: list<FieldType>,
 * }
 */
class PassStructure
{
    /**
     * @param Field[] $headerFields
     * @param Field[] $primaryFields
     * @param Field[] $secondaryFields
     * @param Field[] $auxiliaryFields
     * @param Field[] $backFields
     * @param Field[] $additionalInfoFields
     */
    public function __construct(
        public array $headerFields = [],
        public array $primaryFields = [],
        public array $secondaryFields = [],
        public array $auxiliaryFields = [],
        public array $backFields = [],
        public array $additionalInfoFields = [],
        public ?TransitTypeEnum $transitType = null,
    ) {
    }
}
