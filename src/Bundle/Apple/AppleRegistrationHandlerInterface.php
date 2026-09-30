<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Apple;

/**
 * Optional hook for applications to react to Apple Web Service registration
 * lifecycle events — e.g. cleaning up external push infrastructure (SNS
 * endpoints, subscriptions...), auditing device activity, or propagating
 * state to other systems.
 *
 * Best-effort by design: implementers may throw, the web service contract
 * with the device stays intact (failures are logged and swallowed).
 */
interface AppleRegistrationHandlerInterface
{
    /**
     * A device registered for a pass (new registration).
     */
    public function onDeviceRegistered(string $deviceId, string $passTypeId, string $serialNumber, string $pushToken): void;

    /**
     * A registered device re-registered with a rotated push token.
     */
    public function onPushTokenRotated(
        string $deviceId,
        string $passTypeId,
        string $serialNumber,
        string $previousPushToken,
        string $newPushToken,
    ): void;

    /**
     * A device registration was successfully removed.
     */
    public function onDeviceUnregistered(string $deviceId, string $passTypeId, string $serialNumber): void;
}
