<?php

namespace App\Repository;

use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Session>
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ✅ MÉTHODES POUR L'EMPLOI DU TEMPS (NOUVELLES)
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Trouver les sessions entre deux dates
     */
    /** @return Session[] */
    public function findByDateRange(\DateTimeInterface $debut, \DateTimeInterface $fin): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.dateHeure >= :debut')
            ->andWhere('s.dateHeure <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('s.dateHeure', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Alias pour compatibilité avec l'ancien nom findByPeriode
     */
    /** @return Session[] */
    public function findByPeriode(\DateTimeInterface $debut, \DateTimeInterface $fin): array
    {
        return $this->findByDateRange($debut, $fin);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ✅ VOS MÉTHODES EXISTANTES (reste du fichier inchangé)
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Recherche avancée avec filtres, tri et pagination
     * @param array<string, mixed> $filters
     * @return array<int, Session>
     */
    public function findByFilters(array $filters = [], string $sortBy = 'dateHeure', string $sortOrder = 'DESC', int $page = 1, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.suiviProgressions', 'sp')
            ->addSelect('sp');

        // ============ FILTRES ============
        
        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $qb->andWhere('s.type LIKE :search')
               ->setParameter('search', $search);
        }

        if (!empty($filters['type'])) {
            $qb->andWhere('s.type = :type')
               ->setParameter('type', $filters['type']);
        }

        if (!empty($filters['statut'])) {
            $qb->andWhere('s.statut = :statut')
               ->setParameter('statut', $filters['statut']);
        }

        if (!empty($filters['niveauAgitation'])) {
            $qb->andWhere('s.niveauAgitation = :agitation')
               ->setParameter('agitation', $filters['niveauAgitation']);
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = \DateTime::createFromFormat('Y-m-d\TH:i', $filters['dateFrom']);
            if ($dateFrom) {
                $qb->andWhere('s.dateHeure >= :dateFrom')
                   ->setParameter('dateFrom', $dateFrom);
            }
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = \DateTime::createFromFormat('Y-m-d\TH:i', $filters['dateTo']);
            if ($dateTo) {
                $qb->andWhere('s.dateHeure <= :dateTo')
                   ->setParameter('dateTo', $dateTo);
            }
        }

        if (!empty($filters['dureeMin'])) {
            $qb->andWhere('s.duree >= :dureeMin')
               ->setParameter('dureeMin', (int) $filters['dureeMin']);
        }

        if (!empty($filters['dureeMax'])) {
            $qb->andWhere('s.duree <= :dureeMax')
               ->setParameter('dureeMax', (int) $filters['dureeMax']);
        }

        if (!empty($filters['hasSuivi'])) {
            if ($filters['hasSuivi'] === 'yes') {
                $qb->andWhere('sp.id IS NOT NULL');
            } elseif ($filters['hasSuivi'] === 'no') {
                $qb->andWhere('sp.id IS NULL');
            }
        }

        if (!empty($filters['favori'])) {
            $qb->andWhere('s.favori = true');
        }

        // ============ TRI ============
        $allowedSortFields = [
            'id' => 's.id',
            'dateHeure' => 's.dateHeure',
            'duree' => 's.duree',
            'type' => 's.type',
            'statut' => 's.statut',
            'niveauAgitation' => 's.niveauAgitation',
            'createdAt' => 's.createdAt',
        ];

        $sortField = $allowedSortFields[$sortBy] ?? 's.dateHeure';
        $sortDirection = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $qb->orderBy($sortField, $sortDirection);

        // ============ PAGINATION ============
        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Compte total pour pagination
     */
    /** @param array<string, mixed> $filters */
    public function countByFilters(array $filters = []): int
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(DISTINCT s.id)')
            ->leftJoin('s.suiviProgressions', 'sp');

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $qb->andWhere('s.type LIKE :search')
               ->setParameter('search', $search);
        }

        if (!empty($filters['type'])) {
            $qb->andWhere('s.type = :type')
               ->setParameter('type', $filters['type']);
        }

        if (!empty($filters['statut'])) {
            $qb->andWhere('s.statut = :statut')
               ->setParameter('statut', $filters['statut']);
        }

        if (!empty($filters['niveauAgitation'])) {
            $qb->andWhere('s.niveauAgitation = :agitation')
               ->setParameter('agitation', $filters['niveauAgitation']);
        }

        if (!empty($filters['dateFrom'])) {
            $dateFrom = \DateTime::createFromFormat('Y-m-d\TH:i', $filters['dateFrom']);
            if ($dateFrom) {
                $qb->andWhere('s.dateHeure >= :dateFrom')
                   ->setParameter('dateFrom', $dateFrom);
            }
        }

        if (!empty($filters['dateTo'])) {
            $dateTo = \DateTime::createFromFormat('Y-m-d\TH:i', $filters['dateTo']);
            if ($dateTo) {
                $qb->andWhere('s.dateHeure <= :dateTo')
                   ->setParameter('dateTo', $dateTo);
            }
        }

        if (!empty($filters['dureeMin'])) {
            $qb->andWhere('s.duree >= :dureeMin')
               ->setParameter('dureeMin', (int) $filters['dureeMin']);
        }

        if (!empty($filters['dureeMax'])) {
            $qb->andWhere('s.duree <= :dureeMax')
               ->setParameter('dureeMax', (int) $filters['dureeMax']);
        }

        if (!empty($filters['hasSuivi'])) {
            if ($filters['hasSuivi'] === 'yes') {
                $qb->andWhere('sp.id IS NOT NULL');
            } elseif ($filters['hasSuivi'] === 'no') {
                $qb->andWhere('sp.id IS NULL');
            }
        }

        if (!empty($filters['favori'])) {
            $qb->andWhere('s.favori = true');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Statistiques pour le dashboard
     */
    /** @return array<string, mixed> */
    public function getStatistics(): array
    {
        $total = (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()->getSingleScalarResult();

        $terminees = (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.statut = :statut')
            ->setParameter('statut', 'TERMINEE')
            ->getQuery()->getSingleScalarResult();

        $enCours = (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.statut = :statut')
            ->setParameter('statut', 'EN_COURS')
            ->getQuery()->getSingleScalarResult();

        $sessionsAvecSuivi = (int) $this->createQueryBuilder('s')
            ->select('COUNT(DISTINCT s.id)')
            ->innerJoin('s.suiviProgressions', 'sp')
            ->getQuery()->getSingleScalarResult();

        // Sessions par mois (derniers 12 mois)
        $sessionsParMois = $this->getSessionsPerMonth();

        // Taux de complétion
        $tauxCompletion = $total > 0 ? round(($terminees / $total) * 100, 1) : 0;

        // Durée moyenne
        $dureeMoyenne = $this->getAverageDuration();

        // Sessions par type
        $sessionsParType = $this->getSessionsByType();

        // Nouveaux patients (estimation basée sur sessions uniques par téléphone)
        $nouveauxPatients = $this->getNewPatientsCount();

        // Taux d'absentéisme (sessions annulées)
        $annulees = (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.statut = :statut')
            ->setParameter('statut', 'ANNULEE')
            ->getQuery()->getSingleScalarResult();
        $tauxAbsentéisme = $total > 0 ? round(($annulees / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'terminees' => $terminees,
            'enCours' => $enCours,
            'annulees' => $annulees,
            'planifiees' => (int) $this->createQueryBuilder('s')
                ->select('COUNT(s.id)')
                ->where('s.statut = :statut')
                ->setParameter('statut', 'PLANIFIEE')
                ->getQuery()->getSingleScalarResult(),
            'sessionsAvecSuivi' => $sessionsAvecSuivi,
            'sessionsSansSuivi' => $total - $sessionsAvecSuivi,
            'sessionsParMois' => $sessionsParMois,
            'tauxCompletion' => $tauxCompletion,
            'dureeMoyenne' => $dureeMoyenne,
            'sessionsParType' => $sessionsParType,
            'nouveauxPatients' => $nouveauxPatients,
            'tauxAbsentéisme' => $tauxAbsentéisme,
        ];
    }

    /** @return array<int, array{month: string, count: int}> */
    private function getSessionsPerMonth(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT DATE_FORMAT(date_heure, '%Y-%m') as month, COUNT(*) as count
            FROM session_therapie
            WHERE date_heure >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(date_heure, '%Y-%m')
            ORDER BY month ASC
        ";
        $stmt = $conn->executeQuery($sql);
        /** @var array<array<string, mixed>> $result */
        $result = $stmt->fetchAllAssociative();
        return array_map(fn($row) => ['month' => (string)$row['month'], 'count' => (int)$row['count']], $result);
    }

    private function getAverageDuration(): float
    {
        $result = $this->createQueryBuilder('s')
            ->select('AVG(s.duree) as avg_duration')
            ->getQuery()
            ->getSingleResult();
        return round($result['avg_duration'] ?? 0, 1);
    }

    /** @return array<int, array{type: string, count: int}> */
    private function getSessionsByType(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT type, COUNT(*) as count FROM session_therapie GROUP BY type";
        $stmt = $conn->executeQuery($sql);
        /** @var array<array<string, mixed>> $result */
        $result = $stmt->fetchAllAssociative();
        return array_map(fn($row) => ['type' => (string)$row['type'], 'count' => (int)$row['count']], $result);
    }

    private function getNewPatientsCount(): int
    {
        // Estimation basée sur numéros de téléphone uniques
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT COUNT(DISTINCT telephone_responsable) as count FROM session_therapie WHERE telephone_responsable IS NOT NULL";
        $stmt = $conn->executeQuery($sql);
        $result = $stmt->fetchAssociative();
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Sessions terminées sans suivi de progression depuis X jours
     */
    /** @return Session[] */
    public function findSessionsWithoutProgressTracking(int $days = 1): array
    {
        $dateLimit = new \DateTime();
        $dateLimit->modify("-{$days} days");

        return $this->createQueryBuilder('s')
            ->leftJoin('s.suiviProgressions', 'sp')
            ->where('s.statut = :statut')
            ->andWhere('s.dateHeure <= :dateLimit')
            ->andWhere('sp.id IS NULL') // Pas de suivi de progression
            ->andWhere('s.telephoneResponsable IS NOT NULL') // A un numéro de téléphone
            ->setParameter('statut', 'TERMINEE')
            ->setParameter('dateLimit', $dateLimit)
            ->orderBy('s.dateHeure', 'DESC')
            ->getQuery()
            ->getResult();
    }
}