<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type MessageType 'MESSAGE_TYPE_UNSPECIFIED'|'TEXT'|'TEXT_AND_NOTIFY'|'EXPIRATION_NOTIFICATION'
 */
enum MessageTypeEnum: string
{
    case UNSPECIFIED = 'MESSAGE_TYPE_UNSPECIFIED';
    case TEXT = 'TEXT';
    case TEXT_AND_NOTIFY = 'TEXT_AND_NOTIFY';
    case EXPIRATION_NOTIFICATION = 'EXPIRATION_NOTIFICATION';
}
