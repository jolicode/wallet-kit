<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-type DateStyle 'PKDateStyleNone'|'PKDateStyleShort'|'PKDateStyleMedium'|'PKDateStyleLong'|'PKDateStyleFull'
 */
enum DateStyleEnum: string
{
    case None = 'PKDateStyleNone';
    case Short = 'PKDateStyleShort';
    case Medium = 'PKDateStyleMedium';
    case Long = 'PKDateStyleLong';
    case Full = 'PKDateStyleFull';
}
