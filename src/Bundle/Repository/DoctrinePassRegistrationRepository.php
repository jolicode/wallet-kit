<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Repository;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Jolicode\WalletKit\Bundle\Entity\PassRegistration;

final class DoctrinePassRegistrationRepository implements PassRegistrationRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function register(
        string $deviceId,
        string $passTypeId,
        string $serialNumber,
        string $pushToken,
    ): RegistrationResult {
        $repository = $this->entityManager->getRepository(PassRegistration::class);
        $existing = $repository->findOneBy([
            'deviceId' => $deviceId,
            'passTypeId' => $passTypeId,
            'serialNumber' => $serialNumber,
        ]);

        if (null !== $existing) {
            return $this->updateExistingRegistration($existing, $pushToken);
        }

        $this->entityManager->persist(new PassRegistration($deviceId, $passTypeId, $serialNumber, $pushToken));

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            // Concurrent duplicate: treat as already-registered and still refresh the token.
            $this->entityManager->clear();

            $existing = $repository->findOneBy([
                'deviceId' => $deviceId,
                'passTypeId' => $passTypeId,
                'serialNumber' => $serialNumber,
            ]);

            if (null !== $existing) {
                return $this->updateExistingRegistration($existing, $pushToken);
            }

            // The other transaction deleted its row between our failed insert and re-read.
            $this->entityManager->persist(new PassRegistration($deviceId, $passTypeId, $serialNumber, $pushToken));

            try {
                $this->entityManager->flush();
            } catch (UniqueConstraintViolationException) {
                return RegistrationResult::unchanged();
            }

            return RegistrationResult::created();
        }

        return RegistrationResult::created();
    }

    private function updateExistingRegistration(PassRegistration $registration, string $pushToken): RegistrationResult
    {
        if ($registration->getPushToken() === $pushToken) {
            // Nothing changed: a true no-op — no UPDATE, no flush.
            return RegistrationResult::unchanged();
        }

        $result = RegistrationResult::rotated($registration->getPushToken());
        $registration->updatePushToken($pushToken);
        $this->entityManager->flush();

        return $result;
    }

    public function unregister(string $deviceId, string $passTypeId, string $serialNumber): bool
    {
        $registration = $this->entityManager->getRepository(PassRegistration::class)->findOneBy([
            'deviceId' => $deviceId,
            'passTypeId' => $passTypeId,
            'serialNumber' => $serialNumber,
        ]);

        if (null === $registration) {
            return false;
        }

        $this->entityManager->remove($registration);
        $this->entityManager->flush();

        return true;
    }

    /**
     * @return string[]
     */
    public function findPushTokens(string $passTypeId, string $serialNumber): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('DISTINCT r.pushToken')
            ->from(PassRegistration::class, 'r')
            ->where('r.passTypeId = :passTypeId')
            ->andWhere('r.serialNumber = :serialNumber')
            ->setParameter('passTypeId', $passTypeId)
            ->setParameter('serialNumber', $serialNumber);

        $results = $qb->getQuery()->getSingleColumnResult();

        return array_values($results);
    }

    /**
     * @return string[]
     */
    public function findSerialNumbers(string $deviceId, string $passTypeId): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('r.serialNumber')
            ->from(PassRegistration::class, 'r')
            ->where('r.deviceId = :deviceId')
            ->andWhere('r.passTypeId = :passTypeId')
            ->setParameter('deviceId', $deviceId)
            ->setParameter('passTypeId', $passTypeId);

        $results = $qb->getQuery()->getSingleColumnResult();

        return array_values($results);
    }

    public function unregisterByPushToken(string $pushToken): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(PassRegistration::class, 'r')
            ->where('r.pushToken = :pushToken')
            ->setParameter('pushToken', $pushToken);

        $qb->getQuery()->execute();
    }
}
