<?php
// src/Service/PredictionService.php
namespace App\Service;

use App\Entity\Patient;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class PredictionService
{
    private const SEUIL_RISQUE = 65; // 65%

    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {}

    /**
     * Analyse un patient et retourne le résultat
     * @return array<string, mixed>
     */
    public function analyserPatient(Patient $patient): array
    {
        // 1. Calculer les métriques
        $this->calculerMetriques($patient);

        // 2. Calculer le score de risque (0-100)
        $score = $this->calculerScoreRisque($patient);

        // 3. Déterminer le statut
        $estRisque = $score >= self::SEUIL_RISQUE;
        $statut = $estRisque ? 'RISQUE_ABANDON' : 'NORMAL';

        // 4. Mettre à jour le patient
        $patient->setScoreRisque($score);
        $patient->setStatut($statut);

        $this->em->flush();

        // 5. Logger
        $this->logger->info(sprintf(
            'Patient %s %s: %s (score: %d%%)',
            $patient->getPrenom(),
            $patient->getNom(),
            $statut,
            $score
        ));

        return [
            'patient_id' => $patient->getId(),
            'prediction' => $estRisque ? 1 : 0,
            'statut' => $statut,
            'score_risque' => $score,
            'alerte' => $estRisque
        ];
    }

    /**
     * Calcul les métriques à partir des sessions
     */
    private function calculerMetriques(Patient $patient): void
    {
        $sessions = $patient->getSessions();
        
        // Nombre de sessions
        $patient->setNombreSessions($sessions->count());

        if ($sessions->count() === 0) {
            $patient->setJoursInactivite(999);
            $patient->setTauxCompletion(0);
            return;
        }

        // Dernière session
        $derniere = null;
        $totalExos = 0;
        $totalCompletes = 0;
        $abandons = 0;
        $dureeTotale = 0;

        foreach ($sessions as $session) {
            if (!$derniere || $session->getDateDebut() > $derniere) {
                $derniere = $session->getDateDebut();
            }
            $totalExos += $session->getExercicesTotal();
            $totalCompletes += $session->getExercicesCompletes();
            $dureeTotale += $session->getDureeMinutes();
            if ($session->isEstAbandonnee()) {
                $abandons++;
            }
        }

        // Jours d'inactivité
        $diff = (new \DateTime())->diff($derniere);
        $jours = $diff->days;
        $patient->setJoursInactivite(is_int($jours) ? $jours : 0);
        $patient->setDerniereConnexion($derniere);

        // Taux de completion
        $taux = $totalExos > 0 ? ($totalCompletes / $totalExos) * 100 : 0;
        $patient->setTauxCompletion(round($taux, 1));

        // Exercices abandonnés
        $patient->setExercicesAbandonnes($abandons);

        // Fréquence hebdo (sur 30 derniers jours)
        $ilYa30 = (new \DateTime())->modify('-30 days');
        $recentes = 0;
        foreach ($sessions as $s) {
            if ($s->getDateDebut() >= $ilYa30) {
                $recentes++;
            }
        }
        $freq = round($recentes / 4.3, 1); // 4.3 semaines ~ 30 jours
        $patient->setFrequenceHebdo($freq);
    }

    /**
     * Algorithme simple: score basé sur des règles pondérées
     */
    private function calculerScoreRisque(Patient $patient): int
    {
        $score = 0;

        // Règle 1: Inactivité (max 30 points)
        $jours = $patient->getJoursInactivite();
        if ($jours > 14) {
            $score += 30;
        } elseif ($jours > 7) {
            $score += 20;
        } elseif ($jours > 3) {
            $score += 10;
        }

        // Règle 2: Taux completion faible (max 25 points)
        $taux = $patient->getTauxCompletion();
        if ($taux < 20) {
            $score += 25;
        } elseif ($taux < 50) {
            $score += 15;
        } elseif ($taux < 70) {
            $score += 5;
        }

        // Règle 3: Fréquence faible (max 20 points)
        $freq = $patient->getFrequenceHebdo();
        if ($freq < 0.5) {
            $score += 20;
        } elseif ($freq < 1) {
            $score += 10;
        }

        // Règle 4: Nombre de sessions faible (max 15 points)
        $nb = $patient->getNombreSessions();
        if ($nb < 2) {
            $score += 15;
        } elseif ($nb < 5) {
            $score += 5;
        }

        // Règle 5: Abandons fréquents (max 10 points)
        $abandons = $patient->getExercicesAbandonnes();
        if ($abandons > 5) {
            $score += 10;
        } elseif ($abandons > 2) {
            $score += 5;
        }

        return min(100, $score);
    }

    /**
     * Analyser tous les patients (pour commande ou cron)
     * @return array<int, array<string, mixed>>
     */
    public function analyserTous(): array
    {
        $patients = $this->em->getRepository(Patient::class)->findAll();
        $resultats = [];

        foreach ($patients as $patient) {
            $resultats[] = $this->analyserPatient($patient);
        }

        return $resultats;
    }
}