<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Jolicode\WalletKit\Bundle\Entity\PassRegistration;

final class DoctrinePassRegistrationRepository implements PassRegistrationRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function register(string $deviceId, string $passTypeId, string $serialNumber, string $pushToken): void
    {
        $registration = new PassRegistration($deviceId, $passTypeId, $serialNumber, $pushToken);

        $this->entityManager->persist($registration);
        $this->entityManager->flush();
    }

    public function unregister(string $deviceId, string $passTypeId, string $serialNumber): void
    {
        $registration = $this->entityManager->getRepository(PassRegistration::class)->findOneBy([
            'deviceId' => $deviceId,
            'passTypeId' => $passTypeId,
            'serialNumber' => $serialNumber,
        ]);

        if (null === $registration) {
            return;
        }

        $this->entityManager->remove($registration);
        $this->entityManager->flush();
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
