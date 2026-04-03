<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type MessageType from MessageTypeEnum
 * @phpstan-import-type LocalizedStringType from LocalizedString
 * @phpstan-import-type TimeIntervalType from TimeInterval
 *
 * @phpstan-type GoogleMessageType array{header?: string, body?: string, id?: string, messageType?: MessageType, localizedHeader?: LocalizedStringType, localizedBody?: LocalizedStringType, displayInterval?: TimeIntervalType}
 */
class Message
{
    public function __construct(
        public ?string $header = null,
        public ?string $body = null,
        public ?string $id = null,
        public ?MessageTypeEnum $messageType = null,
        public ?LocalizedString $localizedHeader = null,
        public ?LocalizedString $localizedBody = null,
        public ?TimeInterval $displayInterval = null,
    ) {
    }
}
