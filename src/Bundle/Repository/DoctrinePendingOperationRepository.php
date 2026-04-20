<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Jolicode\WalletKit\Bundle\Entity\PendingOperation;

final class DoctrinePendingOperationRepository implements PendingOperationRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function enqueue(array $operations): void
    {
        foreach ($operations as $operation) {
            $this->entityManager->persist($operation);
        }

        $this->entityManager->flush();
    }

    public function dequeue(string $batchGroupId, int $limit): array
    {
        return $this->entityManager->wrapInTransaction(function () use ($batchGroupId, $limit): array {
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('o')
                ->from(PendingOperation::class, 'o')
                ->where('o.batchGroupId = :batchGroupId')
                ->setParameter('batchGroupId', $batchGroupId)
                ->orderBy('o.id', 'ASC')
                ->setMaxResults($limit);

            /** @var PendingOperation[] $operations */
            $operations = $qb->getQuery()->getResult();

            if (0 === \count($operations)) {
                return [];
            }

            $ids = array_map(static fn (PendingOperation $op): ?int => $op->id, $operations);

            $deleteQb = $this->entityManager->createQueryBuilder();
            $deleteQb->delete(PendingOperation::class, 'o')
                ->where('o.id IN (:ids)')
                ->setParameter('ids', $ids);
            $deleteQb->getQuery()->execute();

            return $operations;
        });
    }

    public function countPending(string $batchGroupId): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(o.id)')
            ->from(PendingOperation::class, 'o')
            ->where('o.batchGroupId = :batchGroupId')
            ->setParameter('batchGroupId', $batchGroupId);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
