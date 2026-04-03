<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-import-type TextAlignment from TextAlignmentEnum
 * @phpstan-import-type DateStyle from DateStyleEnum
 * @phpstan-import-type NumberStyle from NumberStyleEnum
 * @phpstan-import-type SemanticTagsType from SemanticTags
 *
 * @phpstan-type FieldType array{
 *     key: string,
 *     value: string|int|float,
 *     label?: string,
 *     changeMessage?: string,
 *     textAlignment?: TextAlignment,
 *     attributedValue?: string|int|float,
 *     dateStyle?: DateStyle,
 *     timeStyle?: DateStyle,
 *     isRelative?: bool,
 *     currencyCode?: string,
 *     numberStyle?: NumberStyle,
 *     row?: int,
 *     semantics?: SemanticTagsType,
 * }
 */
class Field
{
    public function __construct(
        public string $key,
        public string|int|float $value,
        public ?string $label = null,
        public ?string $changeMessage = null,
        public ?TextAlignmentEnum $textAlignment = null,
        public string|int|float|null $attributedValue = null,
        public ?DateStyleEnum $dateStyle = null,
        public ?DateStyleEnum $timeStyle = null,
        public ?bool $isRelative = null,
        public ?string $currencyCode = null,
        public ?NumberStyleEnum $numberStyle = null,
        public ?int $row = null,
        public ?SemanticTags $semantics = null,
    ) {
    }
}
