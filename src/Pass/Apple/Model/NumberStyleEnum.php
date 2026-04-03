<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-type NumberStyle 'PKNumberStyleDecimal'|'PKNumberStylePercent'|'PKNumberStyleScientific'|'PKNumberStyleSpellOut'
 */
enum NumberStyleEnum: string
{
    case DECIMAL = 'PKNumberStyleDecimal';
    case PERCENT = 'PKNumberStylePercent';
    case SCIENTIFIC = 'PKNumberStyleScientific';
    case SPELL_OUT = 'PKNumberStyleSpellOut';
}
