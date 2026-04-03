<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType;

/**
 * @phpstan-type PersonNameComponentsType array{familyName?: string, givenName?: string, middleName?: string, namePrefix?: string, nameSuffix?: string, nickname?: string, phoneticRepresentation?: string}
 */
class PersonNameComponents
{
    public function __construct(
        public ?string $familyName = null,
        public ?string $givenName = null,
        public ?string $middleName = null,
        public ?string $namePrefix = null,
        public ?string $nameSuffix = null,
        public ?string $nickname = null,
        public ?string $phoneticRepresentation = null,
    ) {
    }
}
