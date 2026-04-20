<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Jolicode\WalletKit\Bundle\WalletPlatformEnum;

#[ORM\Entity]
#[ORM\Table(name: 'wallet_kit_pending_operation')]
#[ORM\Index(columns: ['batch_group_id', 'id'])]
final class PendingOperation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(type: 'string', length: 20, enumType: WalletPlatformEnum::class)]
    public WalletPlatformEnum $platform;

    #[ORM\Column(type: 'string', length: 255)]
    public string $batchGroupId;

    #[ORM\Column(type: 'json')]
    public array $payload;

    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        WalletPlatformEnum $platform,
        string $batchGroupId,
        array $payload,
    ) {
        $this->platform = $platform;
        $this->batchGroupId = $batchGroupId;
        $this->payload = $payload;
        $this->createdAt = new \DateTimeImmutable();
    }
}
