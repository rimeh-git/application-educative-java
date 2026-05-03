<?php

namespace App\Service;

use App\Repository\SessionRepository;

class QrGamificationService
{
    private const POINTS_PRESENCE = 50;

    /** @var array<int, array<string, mixed>> */
    private const BADGES = [
        ['min' => 50,   'icon' => '🌱', 'name' => 'Premier Pas', 'desc' => 'Premiere presence confirmee !', 'color' => '#10b981'],
        ['min' => 150,  'icon' => '🔥', 'name' => 'Assidu',      'desc' => '3 sessions confirmees',         'color' => '#f97316'],
        ['min' => 300,  'icon' => '⭐', 'name' => 'Regulier',    'desc' => '6 sessions confirmees',         'color' => '#f59e0b'],
        ['min' => 500,  'icon' => '🏆', 'name' => 'Champion',    'desc' => '10 sessions confirmees',        'color' => '#8b5cf6'],
        ['min' => 1000, 'icon' => '👑', 'name' => 'Legende',     'desc' => '20 sessions confirmees',        'color' => '#ec4899'],
    ];

    public function __construct(
        private readonly SessionRepository $sessionRepo,
    ) {}

    public function getPointsPresence(): int
    {
        return self::POINTS_PRESENCE;
    }

    public function getTotalPoints(): int
    {
        $total = 0;
        foreach ($this->sessionRepo->findAll() as $s) {
            if ($s->isPresenceConfirmee()) {
                $total += $s->getPointsPresence();
            }
        }
        return $total;
    }

    public function getNbConfirmees(): int
    {
        return count(array_filter(
            $this->sessionRepo->findAll(),
            fn($s) => $s->isPresenceConfirmee()
        ));
    }

    /** @return array<int, array<string, mixed>> */
    public function getBadgesDebloques(int $totalPoints): array
    {
        return array_values(array_filter(
            self::BADGES,
            fn($b) => $totalPoints >= $b['min']
        ));
    }

    /** @return array<string, mixed>|null */
    public function getProchainBadge(int $totalPoints): ?array
    {
        foreach (self::BADGES as $badge) {
            if ($totalPoints < $badge['min']) {
                return array_merge($badge, ['manque' => $badge['min'] - $totalPoints]);
            }
        }
        return null;
    }

    /** @return array<string, mixed> */
    public function getNiveau(int $totalPoints): array
    {
        /** @var array<int, array<string, mixed>> $niveaux */
        $niveaux = [
            ['name' => 'Debutant', 'min' => 0,    'max' => 149,  'emoji' => '🌱', 'color' => '#64748b'],
            ['name' => 'Apprenti', 'min' => 150,  'max' => 349,  'emoji' => '🌿', 'color' => '#3b82f6'],
            ['name' => 'Assidu',   'min' => 350,  'max' => 699,  'emoji' => '🔥', 'color' => '#f97316'],
            ['name' => 'Expert',   'min' => 700,  'max' => 1199, 'emoji' => '⭐', 'color' => '#f59e0b'],
            ['name' => 'Champion', 'min' => 1200, 'max' => 9999, 'emoji' => '👑', 'color' => '#8b5cf6'],
        ];

        foreach ($niveaux as $i => $n) {
            if ($totalPoints >= (int)$n['min'] && $totalPoints <= (int)$n['max']) {
                $next = $niveaux[$i + 1] ?? null;
                // Correction PHPStan : division par zéro si nextMin === min
                $diff = $next !== null ? ((int)$next['min'] - (int)$n['min']) : 1;
                $progress = $next !== null && $diff > 0
                    ? (int) round((($totalPoints - (int)$n['min']) / $diff) * 100)
                    : 100;
                return array_merge($n, [
                    'progress' => $progress,
                    'nextName' => $next['name'] ?? null,
                    'nextMin'  => $next['min'] ?? $totalPoints,
                ]);
            }
        }

        return ['name' => 'Champion', 'emoji' => '👑', 'color' => '#8b5cf6', 'progress' => 100, 'nextName' => null, 'nextMin' => $totalPoints];
    }
}
