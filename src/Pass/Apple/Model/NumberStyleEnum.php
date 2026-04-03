<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-type NumberStyle 'PKNumberStyleDecimal'|'PKNumberStylePercent'|'PKNumberStyleScientific'|'PKNumberStyleSpellOut'
 */
enum NumberStyleEnum: string
{
    case Decimal = 'PKNumberStyleDecimal';
    case Percent = 'PKNumberStylePercent';
    case Scientific = 'PKNumberStyleScientific';
    case SpellOut = 'PKNumberStyleSpellOut';
}
