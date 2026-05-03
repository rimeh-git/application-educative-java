<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTimeImmutable;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /** @return Notification[] */
    public function findUnread(int $limit = 20): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.isRead = false')
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** @return Notification[] */
    public function findCriticalRiskNotifications(int $limit = 50): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.riskScore >= :threshold')
            ->setParameter('threshold', 70)
            ->orderBy('n.riskScore', 'DESC')
            ->addOrderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** @return Notification[] */
    public function findBySession(Session $session): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.session = :session')
            ->setParameter('session', $session)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Notification[] */
    public function findRecentByType(string $type, int $days = 30): array
    {
        $startDate = new DateTimeImmutable("-{$days} days");

        return $this->createQueryBuilder('n')
            ->where('n.type = :type')
            ->andWhere('n.createdAt >= :startDate')
            ->setParameter('type', $type)
            ->setParameter('startDate', $startDate)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countUnread(): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('count(n.id)')
            ->where('n.isRead = false')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countCriticalRisk(): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('count(n.id)')
            ->where('n.riskScore >= :threshold')
            ->andWhere('n.isRead = false')
            ->setParameter('threshold', 70)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function deleteOldNotifications(int $daysOld = 90): int
    {
        $oldDate = new DateTimeImmutable("-{$daysOld} days");

        return $this->createQueryBuilder('n')
            ->delete()
            ->where('n.createdAt < :date')
            ->andWhere('n.isRead = true')
            ->setParameter('date', $oldDate)
            ->getQuery()
            ->execute();
    }
}
