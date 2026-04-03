<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-type TextAlignment 'PKTextAlignmentLeft'|'PKTextAlignmentCenter'|'PKTextAlignmentRight'|'PKTextAlignmentNatural'
 */
enum TextAlignmentEnum: string
{
    case LEFT = 'PKTextAlignmentLeft';
    case CENTER = 'PKTextAlignmentCenter';
    case RIGHT = 'PKTextAlignmentRight';
    case NATURAL = 'PKTextAlignmentNatural';
}
