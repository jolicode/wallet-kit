<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-type TextAlignment 'PKTextAlignmentLeft'|'PKTextAlignmentCenter'|'PKTextAlignmentRight'|'PKTextAlignmentNatural'
 */
enum TextAlignmentEnum: string
{
    case Left = 'PKTextAlignmentLeft';
    case Center = 'PKTextAlignmentCenter';
    case Right = 'PKTextAlignmentRight';
    case Natural = 'PKTextAlignmentNatural';
}
