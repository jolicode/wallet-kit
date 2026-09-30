<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Samsung;

interface SamsungCallbackHandlerInterface
{
    public function onCardStateChanged(string $cardId, string $newState): void;
}
