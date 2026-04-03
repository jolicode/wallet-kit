<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-type BeaconType array{proximityUUID: string, major?: int, minor?: int, relevantText?: string}
 */
class Beacon
{
    public function __construct(
        public string $proximityUUID,
        public ?int $major = null,
        public ?int $minor = null,
        public ?string $relevantText = null,
    ) {
    }
}
