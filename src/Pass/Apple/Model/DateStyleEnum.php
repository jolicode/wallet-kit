<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-type DateStyle 'PKDateStyleNone'|'PKDateStyleShort'|'PKDateStyleMedium'|'PKDateStyleLong'|'PKDateStyleFull'
 */
enum DateStyleEnum: string
{
    case NONE = 'PKDateStyleNone';
    case SHORT = 'PKDateStyleShort';
    case MEDIUM = 'PKDateStyleMedium';
    case LONG = 'PKDateStyleLong';
    case FULL = 'PKDateStyleFull';
}
