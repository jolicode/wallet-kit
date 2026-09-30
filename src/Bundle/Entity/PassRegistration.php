<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'wallet_kit_pass_registration')]
#[ORM\UniqueConstraint(name: 'unique_device_pass', columns: ['device_id', 'pass_type_id', 'serial_number'])]
#[ORM\Index(name: 'push_token_idx', columns: ['push_token'])]
#[ORM\Index(name: 'pass_type_serial_idx', columns: ['pass_type_id', 'serial_number'])]
final class PassRegistration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $deviceId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $passTypeId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $serialNumber;

    #[ORM\Column(type: 'string', length: 255)]
    private string $pushToken;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $registeredAt;

    public function __construct(
        string $deviceId,
        string $passTypeId,
        string $serialNumber,
        string $pushToken,
    ) {
        $this->deviceId = $deviceId;
        $this->passTypeId = $passTypeId;
        $this->serialNumber = $serialNumber;
        $this->pushToken = $pushToken;
        $this->registeredAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getPassTypeId(): string
    {
        return $this->passTypeId;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function getPushToken(): string
    {
        return $this->pushToken;
    }

    /**
     * iOS may re-register with a rotated push token; registrations are unique
     * per (device, pass type, serial) so the token is updatable in place.
     */
    public function updatePushToken(string $pushToken): void
    {
        if ($pushToken !== $this->pushToken) {
            $this->pushToken = $pushToken;
        }
    }

    public function getRegisteredAt(): \DateTimeImmutable
    {
        return $this->registeredAt;
    }
}
