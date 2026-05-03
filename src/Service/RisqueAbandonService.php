<?php

namespace App\Service;

use App\Entity\Session;
use App\Repository\SessionRepository;

class RisqueAbandonService
{
    private const SEUIL_FAIBLE       = 25;
    private const SEUIL_MOYEN        = 50;
    private const SEUIL_ELEVE        = 75;
    private const POIDS_ANNULATION   = 35;
    private const POIDS_PROGRESSION  = 30;
    private const POIDS_AGITATION    = 20;
    private const POIDS_IRREGULARITE = 15;

    public function __construct(
        private readonly SessionRepository $sessionRepository
    ) {}

    /** @param Session[] $sessions @return array<string, mixed> */
    public function analyser(array $sessions): array
    {
        if (empty($sessions)) return $this->retournerVide();

        $sA = $this->calculerScoreAnnulation($sessions);
        $sP = $this->calculerScoreProgression($sessions);
        $sG = $this->calculerScoreAgitation($sessions);
        $sI = $this->calculerScoreIrregularite($sessions);

        // Correction PHPStan : cast en int pour les méthodes qui attendent int
        $scoreGlobal = (int) round(
            ($sA / 100 * self::POIDS_ANNULATION) +
            ($sP / 100 * self::POIDS_PROGRESSION) +
            ($sG / 100 * self::POIDS_AGITATION) +
            ($sI / 100 * self::POIDS_IRREGULARITE)
        );

        return [
            'score'           => $scoreGlobal,
            'niveau'          => $this->determinerNiveau($scoreGlobal),
            'couleur'         => $this->determinerCouleur($scoreGlobal),
            'facteurs'        => [
                'annulation'   => ['score' => $sA, 'poids' => self::POIDS_ANNULATION,   'label' => 'Taux d\'annulation',       'detail' => $this->detailAnnulation($sessions)],
                'progression'  => ['score' => $sP, 'poids' => self::POIDS_PROGRESSION,  'label' => 'Stagnation / Regression',  'detail' => $this->detailProgression($sessions)],
                'agitation'    => ['score' => $sG, 'poids' => self::POIDS_AGITATION,    'label' => 'Niveau d\'agitation',      'detail' => $this->detailAgitation($sessions)],
                'irregularite' => ['score' => $sI, 'poids' => self::POIDS_IRREGULARITE, 'label' => 'Irregularite des seances', 'detail' => $this->detailIrregularite($sessions)],
            ],
            'recommandations' => $this->genererRecommandations($scoreGlobal, $sessions),
            'statistiques'    => $this->calculerStatistiques($sessions),
        ];
    }

    /** @param Session[] $sessions */
    private function calculerScoreAnnulation(array $sessions): int
    {
        $total    = count($sessions);
        $annulees = count(array_filter($sessions, fn(Session $s) => $s->getStatut() === 'ANNULEE'));
        return (int) round($total > 0 ? ($annulees / $total) * 100 : 0);
    }

    /** @param Session[] $sessions */
    private function detailAnnulation(array $sessions): string
    {
        $total    = count($sessions);
        $annulees = count(array_filter($sessions, fn(Session $s) => $s->getStatut() === 'ANNULEE'));
        return "$annulees annulation(s) sur $total seance(s)";
    }

    /** @param Session[] $sessions */
    private function calculerScoreProgression(array $sessions): int
    {
        $progressions = [];
        foreach ($sessions as $session) {
            foreach ($session->getSuiviProgressions() as $suivi) {
                if ($suivi->getProgression() !== null) $progressions[] = $suivi->getProgression();
            }
        }
        if (empty($progressions)) return 70;
        $moyenne = array_sum($progressions) / count($progressions);
        if ($moyenne >= 3)  return 0;
        if ($moyenne >= 1)  return 20;
        if ($moyenne >= 0)  return 50;
        if ($moyenne >= -1) return 75;
        return 100;
    }

    /** @param Session[] $sessions */
    private function detailProgression(array $sessions): string
    {
        $progressions = [];
        foreach ($sessions as $session) {
            foreach ($session->getSuiviProgressions() as $suivi) {
                if ($suivi->getProgression() !== null) $progressions[] = $suivi->getProgression();
            }
        }
        if (empty($progressions)) return 'Aucun suivi enregistre';
        $moyenne = round(array_sum($progressions) / count($progressions), 1);
        return 'Progression moyenne : ' . ($moyenne >= 0 ? '+' : '') . "$moyenne pt(s) sur " . count($progressions) . ' evaluation(s)';
    }

    /** @param Session[] $sessions */
    private function calculerScoreAgitation(array $sessions): int
    {
        $st = array_filter($sessions, fn(Session $s) => in_array($s->getStatut(), ['TERMINEE', 'EN_COURS']));
        if (empty($st)) return 0;
        $total  = count($st);
        $crises = count(array_filter($st, fn(Session $s) => $s->getNiveauAgitation() === 'CRISE'));
        $agites = count(array_filter($st, fn(Session $s) => $s->getNiveauAgitation() === 'AGITE'));
        return (int) round(min((($crises * 3 + $agites) / ($total * 3)) * 100, 100));
    }

    /** @param Session[] $sessions */
    private function detailAgitation(array $sessions): string
    {
        $st     = array_filter($sessions, fn(Session $s) => in_array($s->getStatut(), ['TERMINEE', 'EN_COURS']));
        $crises = count(array_filter($st, fn(Session $s) => $s->getNiveauAgitation() === 'CRISE'));
        $agites = count(array_filter($st, fn(Session $s) => $s->getNiveauAgitation() === 'AGITE'));
        return "$crises crise(s), $agites session(s) agitee(s)";
    }

    /** @param Session[] $sessions */
    private function calculerScoreIrregularite(array $sessions): int
    {
        $sd = array_values(array_filter($sessions, fn(Session $s) => $s->getDateHeure() !== null));
        if (count($sd) < 2) return 0;
        usort($sd, fn($a, $b) => $a->getDateHeure() <=> $b->getDateHeure());
        $gaps = [];
        for ($i = 1; $i < count($sd); $i++) {
            $dA = $sd[$i]->getDateHeure();
            $dB = $sd[$i - 1]->getDateHeure();
            if ($dA !== null && $dB !== null) {
                $diff = $dA->diff($dB);
                $days = $diff->days;
                $gaps[] = is_int($days) ? $days : 0;
            }
        }
        if (empty($gaps)) return 0;
        $gapMoyen = array_sum($gaps) / count($gaps);
        if ($gapMoyen <= 7)  return 0;
        if ($gapMoyen <= 14) return 40;
        if ($gapMoyen <= 21) return 70;
        return 100;
    }

    /** @param Session[] $sessions */
    private function detailIrregularite(array $sessions): string
    {
        $sd = array_values(array_filter($sessions, fn(Session $s) => $s->getDateHeure() !== null));
        if (count($sd) < 2) return 'Pas assez de donnees';
        usort($sd, fn($a, $b) => $a->getDateHeure() <=> $b->getDateHeure());
        $gaps = [];
        for ($i = 1; $i < count($sd); $i++) {
            $dA = $sd[$i]->getDateHeure();
            $dB = $sd[$i - 1]->getDateHeure();
            if ($dA !== null && $dB !== null) {
                $diff = $dA->diff($dB);
                $days = $diff->days;
                $gaps[] = is_int($days) ? $days : 0;
            }
        }
        if (empty($gaps)) return 'Pas assez de donnees';
        return 'Intervalle moyen : ' . round(array_sum($gaps) / count($gaps), 1) . ' jour(s)';
    }

    private function determinerNiveau(int $score): string
    {
        return match(true) {
            $score <= self::SEUIL_FAIBLE => 'FAIBLE',
            $score <= self::SEUIL_MOYEN  => 'MOYEN',
            $score <= self::SEUIL_ELEVE  => 'ELEVE',
            default                      => 'CRITIQUE',
        };
    }

    private function determinerCouleur(int $score): string
    {
        return match(true) {
            $score <= self::SEUIL_FAIBLE => 'success',
            $score <= self::SEUIL_MOYEN  => 'warning',
            $score <= self::SEUIL_ELEVE  => 'danger',
            default                      => 'dark',
        };
    }

    /** @param Session[] $sessions @return string[] */
    private function genererRecommandations(int $score, array $sessions): array
    {
        $r = [];
        if ($this->calculerScoreAnnulation($sessions) >= 50)   $r[] = 'Contacter le responsable pour les absences repetees.';
        if ($this->calculerScoreProgression($sessions) >= 70)  $r[] = 'Revoir les objectifs — progres insuffisants.';
        if ($this->calculerScoreAgitation($sessions) >= 60)    $r[] = 'Adapter la technique — agitation trop elevee.';
        if ($this->calculerScoreIrregularite($sessions) >= 70) $r[] = 'Rappeler l\'importance de la regularite.';
        if ($score >= self::SEUIL_ELEVE)                       $r[] = 'Reunion de bilan avec les parents urgente.';
        if (empty($r)) $r[] = 'Suivi satisfaisant — continuer sur la meme dynamique.';
        return $r;
    }

    /** @param Session[] $sessions @return array<string, mixed> */
    private function calculerStatistiques(array $sessions): array
    {
        $terminees = count(array_filter($sessions, fn($s) => $s->getStatut() === 'TERMINEE'));
        $annulees  = count(array_filter($sessions, fn($s) => $s->getStatut() === 'ANNULEE'));
        $totalSuivis = 0;
        /** @var array<string, int[]> $domainesProgression */
        $domainesProgression = [];
        foreach ($sessions as $session) {
            foreach ($session->getSuiviProgressions() as $suivi) {
                $totalSuivis++;
                $d = $suivi->getDomaine() ?? 'inconnu';
                if (!isset($domainesProgression[$d])) $domainesProgression[$d] = [];
                if ($suivi->getProgression() !== null) $domainesProgression[$d][] = $suivi->getProgression();
            }
        }
        $progressionParDomaine = [];
        foreach ($domainesProgression as $d => $vals) {
            $progressionParDomaine[$d] = !empty($vals) ? round(array_sum($vals) / count($vals), 1) : null;
        }
        return [
            'total_sessions'          => count($sessions),
            'sessions_terminees'      => $terminees,
            'sessions_annulees'       => $annulees,
            'total_suivis'            => $totalSuivis,
            'progression_par_domaine' => $progressionParDomaine,
        ];
    }

    /** @return array<string, mixed> */
    private function retournerVide(): array
    {
        return [
            'score' => 0, 'niveau' => 'INCONNU', 'couleur' => 'secondary',
            'facteurs' => [], 'recommandations' => ['Aucune donnee disponible.'],
            'statistiques' => ['total_sessions' => 0, 'sessions_terminees' => 0, 'sessions_annulees' => 0, 'total_suivis' => 0, 'progression_par_domaine' => []],
        ];
    }
}
