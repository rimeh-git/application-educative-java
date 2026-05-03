<?php

namespace App\Repository;

use App\Entity\SuiviProgression;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SuiviProgression>
 */
class SuiviProgressionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuiviProgression::class);
    }

    /**
     * Recherche avancée avec filtres, tri et pagination
     * @param array<string, mixed> $filters
     * @return array<int, SuiviProgression>
     */
    public function findByFilters(array $filters = [], string $sortBy = 'dateEvaluation', string $sortOrder = 'DESC', int $page = 1, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('sp')
            ->leftJoin('sp.session', 's')
            ->addSelect('s');

        // ============ FILTRES ============

        // 1. Recherche texte
        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $qb->andWhere(
                $qb->expr()->orX(
                    'sp.domaine LIKE :search',
                    'sp.comportementsObserves LIKE :search',
                    'sp.declencheursIdentifies LIKE :search',
                    'sp.objectifsRealises LIKE :search',
                    'sp.recommandationsParent LIKE :search',
                    's.type LIKE :search',
                    'CAST(sp.id AS STRING) LIKE :search',
                    'CAST(s.id AS STRING) LIKE :search'
                )
            )->setParameter('search', $search);
        }

        // 2. Filtre par domaine
        if (!empty($filters['domaine'])) {
            $qb->andWhere('sp.domaine = :domaine')
               ->setParameter('domaine', $filters['domaine']);
        }

        // 3. Filtre par session
        if (!empty($filters['sessionId'])) {
            $qb->andWhere('s.id = :sessionId')
               ->setParameter('sessionId', (int) $filters['sessionId']);
        }

        // 4. Filtre par type de session
        if (!empty($filters['sessionType'])) {
            $qb->andWhere('s.type = :sessionType')
               ->setParameter('sessionType', $filters['sessionType']);
        }

        // 5. Filtre par date
        if (!empty($filters['dateEvalFrom'])) {
            $dateFrom = \DateTime::createFromFormat('Y-m-d', $filters['dateEvalFrom']);
            if ($dateFrom) {
                $qb->andWhere('sp.dateEvaluation >= :dateEvalFrom')
                   ->setParameter('dateEvalFrom', $dateFrom->setTime(0, 0, 0));
            }
        }
        
        if (!empty($filters['dateEvalTo'])) {
            $dateTo = \DateTime::createFromFormat('Y-m-d', $filters['dateEvalTo']);
            if ($dateTo) {
                $qb->andWhere('sp.dateEvaluation <= :dateEvalTo')
                   ->setParameter('dateEvalTo', $dateTo->setTime(23, 59, 59));
            }
        }

        // 6. Filtre par scores
        if (!empty($filters['scoreAvantMin'])) {
            $qb->andWhere('sp.scoreAvant >= :scoreAvantMin')
               ->setParameter('scoreAvantMin', (int) $filters['scoreAvantMin']);
        }
        if (!empty($filters['scoreAvantMax'])) {
            $qb->andWhere('sp.scoreAvant <= :scoreAvantMax')
               ->setParameter('scoreAvantMax', (int) $filters['scoreAvantMax']);
        }

        if (!empty($filters['scoreApresMin'])) {
            $qb->andWhere('sp.scoreApres >= :scoreApresMin')
               ->setParameter('scoreApresMin', (int) $filters['scoreApresMin']);
        }
        if (!empty($filters['scoreApresMax'])) {
            $qb->andWhere('sp.scoreApres <= :scoreApresMax')
               ->setParameter('scoreApresMax', (int) $filters['scoreApresMax']);
        }

        // 7. Filtre par progression
        if (!empty($filters['progressionMin'])) {
            $qb->andWhere('(sp.scoreApres - sp.scoreAvant) >= :progressionMin')
               ->setParameter('progressionMin', (int) $filters['progressionMin']);
        }
        if (!empty($filters['progressionMax'])) {
            $qb->andWhere('(sp.scoreApres - sp.scoreAvant) <= :progressionMax')
               ->setParameter('progressionMax', (int) $filters['progressionMax']);
        }

        // 8. Filtre: progression positive/négative/nulle
        if (!empty($filters['progressionType'])) {
            match($filters['progressionType']) {
                'positive' => $qb->andWhere('sp.scoreApres > sp.scoreAvant'),
                'negative' => $qb->andWhere('sp.scoreApres < sp.scoreAvant'),
                'stable' => $qb->andWhere('sp.scoreApres = sp.scoreAvant'),
                default => null
            };
        }

        // ============ TRI ============
        $allowedSortFields = [
            'id' => 'sp.id',
            'dateEvaluation' => 'sp.dateEvaluation',
            'domaine' => 'sp.domaine',
            'scoreAvant' => 'sp.scoreAvant',
            'scoreApres' => 'sp.scoreApres',
            'progression' => '(sp.scoreApres - sp.scoreAvant)',
            'session' => 's.dateHeure',
        ];

        $sortField = $allowedSortFields[$sortBy] ?? 'sp.dateEvaluation';
        $sortDirection = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
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
        $qb = $this->createQueryBuilder('sp')
            ->select('COUNT(sp.id)')
            ->leftJoin('sp.session', 's');

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $qb->andWhere(
                $qb->expr()->orX(
                    'sp.domaine LIKE :search',
                    'sp.comportementsObserves LIKE :search',
                    'sp.declencheursIdentifies LIKE :search',
                    'sp.objectifsRealises LIKE :search',
                    'sp.recommandationsParent LIKE :search',
                    's.type LIKE :search',
                    'CAST(sp.id AS STRING) LIKE :search',
                    'CAST(s.id AS STRING) LIKE :search'
                )
            )->setParameter('search', $search);
        }

        if (!empty($filters['domaine'])) {
            $qb->andWhere('sp.domaine = :domaine')->setParameter('domaine', $filters['domaine']);
        }
        if (!empty($filters['sessionId'])) {
            $qb->andWhere('s.id = :sessionId')->setParameter('sessionId', (int) $filters['sessionId']);
        }
        if (!empty($filters['sessionType'])) {
            $qb->andWhere('s.type = :sessionType')->setParameter('sessionType', $filters['sessionType']);
        }
        if (!empty($filters['dateEvalFrom'])) {
            $dateFrom = \DateTime::createFromFormat('Y-m-d', $filters['dateEvalFrom']);
            if ($dateFrom) {
                $qb->andWhere('sp.dateEvaluation >= :dateEvalFrom')->setParameter('dateEvalFrom', $dateFrom->setTime(0, 0, 0));
            }
        }
        if (!empty($filters['dateEvalTo'])) {
            $dateTo = \DateTime::createFromFormat('Y-m-d', $filters['dateEvalTo']);
            if ($dateTo) {
                $qb->andWhere('sp.dateEvaluation <= :dateEvalTo')->setParameter('dateEvalTo', $dateTo->setTime(23, 59, 59));
            }
        }
        if (!empty($filters['scoreAvantMin'])) {
            $qb->andWhere('sp.scoreAvant >= :scoreAvantMin')->setParameter('scoreAvantMin', (int) $filters['scoreAvantMin']);
        }
        if (!empty($filters['scoreAvantMax'])) {
            $qb->andWhere('sp.scoreAvant <= :scoreAvantMax')->setParameter('scoreAvantMax', (int) $filters['scoreAvantMax']);
        }
        if (!empty($filters['scoreApresMin'])) {
            $qb->andWhere('sp.scoreApres >= :scoreApresMin')->setParameter('scoreApresMin', (int) $filters['scoreApresMin']);
        }
        if (!empty($filters['scoreApresMax'])) {
            $qb->andWhere('sp.scoreApres <= :scoreApresMax')->setParameter('scoreApresMax', (int) $filters['scoreApresMax']);
        }
        if (!empty($filters['progressionType'])) {
            match($filters['progressionType']) {
                'positive' => $qb->andWhere('sp.scoreApres > sp.scoreAvant'),
                'negative' => $qb->andWhere('sp.scoreApres < sp.scoreAvant'),
                'stable' => $qb->andWhere('sp.scoreApres = sp.scoreAvant'),
                default => null
            };
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Statistiques pour le dashboard
     */
    /** @return array<string, mixed> */
    public function getStatistics(): array
    {
        $total = (int) $this->createQueryBuilder('sp')
            ->select('COUNT(sp.id)')
            ->getQuery()->getSingleScalarResult();

        $avgProgression = (float) $this->createQueryBuilder('sp')
            ->select('AVG(sp.scoreApres - sp.scoreAvant)')
            ->getQuery()->getSingleScalarResult();

        $parDomaine = $this->createQueryBuilder('sp')
            ->select('sp.domaine as domaine, COUNT(sp.id) as count, AVG(sp.scoreApres - sp.scoreAvant) as avgProgression')
            ->groupBy('sp.domaine')
            ->getQuery()
            ->getResult();

        $progressionPositive = (int) $this->createQueryBuilder('sp')
            ->select('COUNT(sp.id)')
            ->where('sp.scoreApres > sp.scoreAvant')
            ->getQuery()->getSingleScalarResult();

        $progressionNegative = (int) $this->createQueryBuilder('sp')
            ->select('COUNT(sp.id)')
            ->where('sp.scoreApres < sp.scoreAvant')
            ->getQuery()->getSingleScalarResult();

        $progressionStable = (int) $this->createQueryBuilder('sp')
            ->select('COUNT(sp.id)')
            ->where('sp.scoreApres = sp.scoreAvant')
            ->getQuery()->getSingleScalarResult();

        return [
            'total' => $total,
            'avgProgression' => round($avgProgression, 2),
            'parDomaine' => $parDomaine,
            'progressionPositive' => $progressionPositive,
            'progressionNegative' => $progressionNegative,
            'progressionStable' => $progressionStable,
        ];
    }

    /**
     * Derniers suivis
     */
    /** @return SuiviProgression[] */
    public function findRecent(int $limit = 5): array
    {
        return $this->createQueryBuilder('sp')
            ->leftJoin('sp.session', 's')
            ->addSelect('s')
            ->orderBy('sp.dateEvaluation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}