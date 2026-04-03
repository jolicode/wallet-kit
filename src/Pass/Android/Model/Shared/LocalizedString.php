<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type TranslatedStringType from TranslatedString
 *
 * @phpstan-type LocalizedStringType array{defaultValue?: TranslatedStringType, translatedValues?: list<TranslatedStringType>}
 */
class LocalizedString
{
    /**
     * @param list<TranslatedString>|null $translatedValues
     */
    public function __construct(
        public ?TranslatedString $defaultValue = null,
        public ?array $translatedValues = null,
    ) {
    }
}
