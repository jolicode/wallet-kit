<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Google;

interface GoogleCallbackHandlerInterface
{
    public function onPassSaved(string $classId, string $objectId): void;

    public function onPassDeleted(string $classId, string $objectId): void;
}
