<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder\Internal;

use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TranslatedString;

final class LocalizedStringHelper
{
    public static function en(string $value): LocalizedString
    {
        return new LocalizedString(new TranslatedString('en', $value));
    }
}
