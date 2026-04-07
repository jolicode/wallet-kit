<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type RotatingBarcodeValuesType array{startDateTime?: string, values?: list<string>, periodMillis?: string}
 */
class RotatingBarcodeValues
{
    /**
     * @param list<string>|null $values
     */
    public function __construct(
        public ?string $startDateTime = null,
        public ?array $values = null,
        public ?string $periodMillis = null,
    ) {
    }
}
