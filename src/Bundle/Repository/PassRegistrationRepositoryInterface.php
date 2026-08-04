<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Repository;

interface PassRegistrationRepositoryInterface
{
    /**
     * @return bool true if a new registration was created, false if it already existed
     */
    public function register(string $deviceId, string $passTypeId, string $serialNumber, string $pushToken): bool;

    public function unregister(string $deviceId, string $passTypeId, string $serialNumber): void;

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
