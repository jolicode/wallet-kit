<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type MessageType 'TEXT'|'TEXT_AND_NOTIFY'
 */
enum MessageTypeEnum: string
{
    case TEXT = 'TEXT';
    case TEXT_AND_NOTIFY = 'TEXT_AND_NOTIFY';
}
