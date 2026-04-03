<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type TranslatedStringType array{language?: string, value?: string}
 */
class TranslatedString
{
    public function __construct(
        public ?string $language = null,
        public ?string $value = null,
    ) {
    }
}
