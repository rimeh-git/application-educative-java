<?php

namespace App\Repository;

use App\Entity\MoodTracking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MoodTracking>
 */
class MoodTrackingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoodTracking::class);
    }

    public function findByPatientLastDays(int $patientId, int $days = 7): array
    {
        $date = new \DateTime("-{$days} days");
        return $this->createQueryBuilder('m')
            ->andWhere('m.patient = :patientId')
            ->andWhere('m.createdAt >= :date')
            ->setParameter('patientId', $patientId)
            ->setParameter('date', $date)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getMoodStats(): array
    {
        return $this->createQueryBuilder('m')
            ->select('m.mood, COUNT(m.id) as count')
            ->groupBy('m.mood')
            ->getQuery()
            ->getResult();
    }
}
