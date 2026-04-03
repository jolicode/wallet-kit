<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type MessageType 'TEXT'|'TEXT_AND_NOTIFY'
 */
enum MessageTypeEnum: string
{
    case Text = 'TEXT';
    case TextAndNotify = 'TEXT_AND_NOTIFY';
}
