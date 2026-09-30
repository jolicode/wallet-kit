<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Repository;

interface PassRegistrationRepositoryInterface
{
    /**
     * @return RegistrationResult whether a new registration was created,
     *                            an existing one saw its push token rotated (with the previous
     *                            token), or nothing changed
     */
    public function register(
        string $deviceId,
        string $passTypeId,
        string $serialNumber,
        string $pushToken,
    ): RegistrationResult;

    public function unregister(string $deviceId, string $passTypeId, string $serialNumber): bool;

    /**
     * @return string[] Push tokens
     */
    public function findPushTokens(string $passTypeId, string $serialNumber): array;

    /**
     * @return string[] Serial numbers
     */
    public function findSerialNumbers(string $deviceId, string $passTypeId): array;

    public function unregisterByPushToken(string $pushToken): void;
}
