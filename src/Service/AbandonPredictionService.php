<?php

namespace App\Service;

use App\Entity\Session;
use App\Repository\SessionRepository;
use App\Repository\SuiviProgressionRepository;

class AbandonPredictionService
{
    private const SEUIL = 0.50;

    public function __construct(
        private readonly SessionRepository $sessionRepo,
        private readonly SuiviProgressionRepository $suiviRepo,
    ) {}

    /** @return array<string, mixed> */
    public function predire(Session $session): array
    {
        $features = $this->extraireFeatures($session);
        $score    = $this->calculerScore($features);

        $prediction = $score >= self::SEUIL ? 1 : 0;
        $statut     = $prediction === 1 ? 'RISQUE_ABANDON' : 'NORMAL';
        $label      = $prediction === 1 ? 'RISQUE_ABANDON' : 'NORMAL';

        return [
            'prediction'  => $prediction,
            'statut'      => $statut,
            'label'       => $label,
            'probabilite' => (int) round($score * 100),
            'score'       => round($score, 3),
            'features'    => $features,
            'facteurs'    => $this->expliquerFacteurs($features),
        ];
    }

    /** @return array<string, mixed> */
    public function analyserTout(): array
    {
        $sessions  = $this->sessionRepo->findAll();
        $resultats = [];
        $stables   = 0;
        $risques   = 0;

        foreach ($sessions as $session) {
            $pred = $this->predire($session);
            $resultats[] = ['session' => $session, 'prediction' => $pred];
            $pred['prediction'] === 1 ? $risques++ : $stables++;
        }

        usort($resultats, function ($a, $b) {
            if ($a['prediction']['prediction'] !== $b['prediction']['prediction']) {
                return $b['prediction']['prediction'] - $a['prediction']['prediction'];
            }
            return $b['prediction']['probabilite'] - $a['prediction']['probabilite'];
        });

        return [
            'resultats'  => $resultats,
            'total'      => count($sessions),
            'stables'    => $stables,
            'risques'    => $risques,
            'tauxRisque' => count($sessions) > 0 ? round(($risques / count($sessions)) * 100, 1) : 0,
        ];
    }

    /**
     * @param Session $session
     * @return array<string, mixed>
     */
    private function extraireFeatures(Session $session): array
    {
        $suivis      = $session->getSuiviProgressions()->toArray();
        $progressions = array_filter(
            array_map(fn($s) => $s->getProgression(), $suivis),
            fn($p) => $p !== null
        );

        $progMoyenne   = !empty($progressions) ? array_sum($progressions) / count($progressions) : null;
        $scoreApresMin = null;
        foreach ($suivis as $s) {
            $v = $s->getScoreApres();
            if ($v !== null && ($scoreApresMin === null || $v < $scoreApresMin)) {
                $scoreApresMin = $v;
            }
        }

        return [
            'statut'          => $session->getStatut(),
            'niveauAgitation' => $session->getNiveauAgitation(),
            'duree'           => $session->getDuree() ?? 0,
            'hasObjectif'     => !empty($session->getObjectifSeance()),
            'hasEmail'        => !empty($session->getParentEmail()),
            'hasTel'          => !empty($session->getTelephoneResponsable()),
            'nbSuivis'        => count($suivis),
            'progMoyenne'     => $progMoyenne,
            'scoreApresMin'   => $scoreApresMin,
        ];
    }

    /** @param array<string, mixed> $f */
    private function calculerScore(array $f): float
    {
        $score = 0.0;

        // Statut (30%)
        $score += match($f['statut']) {
            'ANNULEE'   => 0.30,
            'PLANIFIEE' => 0.18,
            'EN_COURS'  => 0.08,
            'TERMINEE'  => 0.0,
            default     => 0.15,
        };

        // Agitation (25%)
        $score += match($f['niveauAgitation']) {
            'CRISE' => 0.25,
            'AGITE' => 0.14,
            'CALME' => 0.0,
            default => 0.08,
        };

        // Duree (15%)
        $score += match(true) {
            $f['duree'] <= 0  => 0.15,
            $f['duree'] < 20  => 0.12,
            $f['duree'] < 30  => 0.07,
            $f['duree'] <= 90 => 0.0,
            default           => 0.05,
        };

        // Progression (20%)
        if ($f['progMoyenne'] === null) {
            $score += 0.15;
        } elseif ($f['progMoyenne'] < -1) {
            $score += 0.20;
        } elseif ($f['progMoyenne'] < 0) {
            $score += 0.12;
        } elseif ($f['progMoyenne'] == 0) {
            $score += 0.06;
        }

        // Score apres faible (5%)
        if ($f['scoreApresMin'] !== null && $f['scoreApresMin'] <= 3) {
            $score += 0.05;
        }

        // Completude (5%)
        if (!$f['hasObjectif']) $score += 0.02;
        if (!$f['hasEmail'])    $score += 0.02;
        if (!$f['hasTel'])      $score += 0.01;

        return min(1.0, $score);
    }

    /**
     * @param array<string, mixed> $f
     * @return array<int, array<string, mixed>>
     */
    private function expliquerFacteurs(array $f): array
    {
        $facteurs = [];

        if ($f['statut'] === 'ANNULEE') {
            $facteurs[] = ['label' => 'Session annulee', 'niveau' => 'critique', 'icone' => 'X'];
        }
        if ($f['niveauAgitation'] === 'CRISE') {
            $facteurs[] = ['label' => 'Niveau de crise', 'niveau' => 'critique', 'icone' => '!'];
        } elseif ($f['niveauAgitation'] === 'AGITE') {
            $facteurs[] = ['label' => 'Enfant agite', 'niveau' => 'moyen', 'icone' => '~'];
        }
        if ($f['duree'] < 20 && $f['duree'] > 0) {
            $facteurs[] = ['label' => 'Seance courte (' . $f['duree'] . ' min)', 'niveau' => 'moyen', 'icone' => 'T'];
        }
        if ($f['progMoyenne'] === null) {
            $facteurs[] = ['label' => 'Aucun suivi', 'niveau' => 'moyen', 'icone' => '-'];
        } elseif ($f['progMoyenne'] < 0) {
            $facteurs[] = ['label' => 'Regression (' . round($f['progMoyenne'], 1) . ' pts)', 'niveau' => 'critique', 'icone' => 'v'];
        }
        if (!$f['hasObjectif']) {
            $facteurs[] = ['label' => 'Aucun objectif', 'niveau' => 'faible', 'icone' => 'o'];
        }
        if (!$f['hasEmail'] && !$f['hasTel']) {
            $facteurs[] = ['label' => 'Aucun contact', 'niveau' => 'moyen', 'icone' => 'c'];
        }

        return $facteurs;
    }
}
