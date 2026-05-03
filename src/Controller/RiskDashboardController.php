<?php

namespace App\Controller;

use App\Entity\Session;
use App\Repository\SessionRepository;
use App\Repository\SuiviProgressionRepository;
use App\Service\RisqueAbandonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/risk')]
class RiskDashboardController extends AbstractController
{
    public function __construct(
        private RisqueAbandonService       $risqueService,
        private SessionRepository          $sessionRepo,
        private SuiviProgressionRepository $suiviRepo,
        private EntityManagerInterface     $em,
    ) {}

    #[Route('', name: 'app_risk_dashboard')]
    public function dashboard(): Response
    {
        $sessions = $this->sessionRepo->findAll();
        $suivis   = $this->suiviRepo->findAll();
        $today    = new \DateTime();

        // ── Prédictions par session ──
        $stables  = [];
        $alertes  = [];
        $tableau  = [];

        foreach ($sessions as $session) {
            $pred = $this->calculateRisk($session);

            // Progression moyenne de cette session
            $progList = array_filter(
                array_map(fn($s) => $s->getProgression(), $session->getSuiviProgressions()->toArray()),
                fn($p) => $p !== null
            );
            $progMoy = count($progList) > 0 ? round(array_sum($progList) / count($progList), 1) : null;

            $entry = [
                'session'     => $session,
                'prediction'  => $pred['prediction'],
                'probabilite' => $pred['probabilite'],
                'statut'      => $pred['statut'],
                'label'       => $pred['label'],
                'progMoy'     => $progMoy,
            ];

            $tableau[] = $entry;

            if ($pred['prediction'] === 1)      $alertes[] = $entry;
            elseif ($pred['prediction'] === 0)  $stables[] = $entry;
        }

        usort($alertes, fn($a, $b) => $b['probabilite'] <=> $a['probabilite']);

        // ── KPIs ──
        $sessionsAujourdhui = array_filter(
            $sessions,
            fn($s) => $s->getDateHeure() && $s->getDateHeure()->format('Y-m-d') === $today->format('Y-m-d')
        );

        $allProgressions = array_filter(
            array_map(fn($s) => $s->getProgression(), $suivis),
            fn($p) => $p !== null
        );
        $tauxEvolution = count($allProgressions) > 0
            ? round(array_sum($allProgressions) / count($allProgressions), 1)
            : 0;

        // ── Analyse intelligente ──
        $topAmeliore  = null;
        $topRisque    = null;
        $bestProg     = PHP_INT_MIN;
        $worstProb    = -1;

        foreach ($tableau as $entry) {
            if ($entry['progMoy'] !== null && $entry['progMoy'] > $bestProg) {
                $bestProg    = $entry['progMoy'];
                $topAmeliore = $entry;
            }
            if ($entry['probabilite'] > $worstProb) {
                $worstProb = $entry['probabilite'];
                $topRisque = $entry;
            }
        }

        // ── Données Chart.js ──
        $chartLabels = [];
        $chartData   = [];
        $recent = array_slice(array_filter($tableau, fn($e) => $e['progMoy'] !== null), 0, 8);
        foreach ($recent as $e) {
            $chartLabels[] = 'Session #' . $e['session']->getId();
            $chartData[]   = $e['progMoy'];
        }

        // ── Notifications ──
        $notifications = [];
        foreach (array_slice($alertes, 0, 3) as $a) {
            $notifications[] = [
                'type'    => 'danger',
                'icon'    => 'fa-exclamation-triangle',
                'message' => 'Session #' . $a['session']->getId() . ' — risque abandon ' . $a['probabilite'] . '%',
            ];
        }
        foreach (array_slice($sessionsAujourdhui, 0, 2) as $s) {
            $notifications[] = [
                'type'    => 'info',
                'icon'    => 'fa-calendar',
                'message' => 'Session #' . $s->getId() . ' prévue aujourd\'hui (' . $s->getType() . ')',
            ];
        }

        return $this->render('risk/dashboard.html.twig', [
            'kpis' => [
                'stables'       => count($stables),
                'alertes'       => count($alertes),
                'sessionsDuJour'=> count($sessionsAujourdhui),
                'tauxEvolution' => $tauxEvolution,
            ],
            'alertes'       => $alertes,
            'tableau'       => $tableau,
            'topAmeliore'   => $topAmeliore,
            'topRisque'     => $topRisque,
            'moyenneProg'   => $tauxEvolution,
            'notifications' => $notifications,
            'chartLabels'   => json_encode($chartLabels),
            'chartData'     => json_encode($chartData),
            'chartNormal'   => count($stables),
            'chartRisque'   => count($alertes),
            'analyse'       => $this->risqueService->analyser($sessions),
        ]);
    }

    /**
     * Calcule le risque d'abandon pour une session
     * @return array{prediction: int, probabilite: float, statut: string, label: string}
     */
    private function calculateRisk(Session $session): array
    {
        $score = 0;
        
        // Statut (30%)
        $statutScore = match($session->getStatut()) {
            'ANNULEE' => 100,
            'PLANIFIEE' => 60,
            'EN_COURS' => 20,
            'TERMINEE' => 0,
            default => 50
        };
        $score += $statutScore * 0.30;
        
        // Agitation (30%)
        $agitationScore = match($session->getNiveauAgitation()) {
            'CRISE' => 90,
            'AGITE' => 50,
            'CALME' => 5,
            default => 30
        };
        $score += $agitationScore * 0.30;
        
        // Durée (20%)
        $duree = $session->getDuree() ?? 0;
        $dureeScore = match(true) {
            $duree <= 0 => 80,
            $duree < 20 => 70,
            $duree < 30 => 40,
            $duree <= 90 => 5,
            $duree <= 120 => 20,
            default => 50
        };
        $score += $dureeScore * 0.20;
        
        // Complétude (20%)
        $filled = 0;
        if ($session->getObjectifSeance()) $filled++;
        if ($session->getNotes()) $filled++;
        if ($session->getTechniqueUtilisee()) $filled++;
        if ($session->getParentEmail()) $filled++;
        if ($session->getTelephoneResponsable()) $filled++;
        
        $completudeScore = match(true) {
            $filled >= 4 => 0,
            $filled === 3 => 20,
            $filled === 2 => 45,
            $filled === 1 => 70,
            default => 90
        };
        $score += $completudeScore * 0.20;
        
        $prediction = $score >= 50 ? 1 : 0;
        $probabilite = round($score, 1);
        
        $level = match(true) {
            $score >= 80 => ['statut' => 'CRITIQUE', 'label' => 'Risque Critique'],
            $score >= 65 => ['statut' => 'TRES_ELEVE', 'label' => 'Risque Très Élevé'],
            $score >= 45 => ['statut' => 'ELEVE', 'label' => 'Risque Élevé'],
            $score >= 25 => ['statut' => 'MODERE', 'label' => 'Risque Modéré'],
            default => ['statut' => 'FAIBLE', 'label' => 'Risque Faible'],
        };
        
        return [
            'prediction' => $prediction,
            'probabilite' => $probabilite,
            'statut' => $level['statut'],
            'label' => $level['label'],
        ];
    }

    #[Route('/session/{id}/propose-support', name: 'app_risk_propose_support', methods: ['POST'])]
    public function proposeSupport(Session $session, Request $request): JsonResponse
    {
        $support = new Session();
        $type = $session->getType();
        if ($type !== null) {
            $support->setType($type);
        }
        $support->setStatut('PLANIFIEE');
        $support->setDuree(45);
        $support->setNiveauAgitation('CALME');
        $support->setObjectifSeance('Session de soutien — risque d\'abandon détecté');
        
        $messageRaw = $request->request->get('message', '');
        $support->setNotes(is_string($messageRaw) ? $messageRaw : '');
        
        $support->setNomResponsable($session->getNomResponsable());
        $support->setParentEmail($session->getParentEmail());
        $support->setTelephoneResponsable($session->getTelephoneResponsable());

        try {
            $dateRaw = $request->request->get('date', '+3 days');
            $support->setDateHeure(new \DateTime(is_string($dateRaw) ? $dateRaw : '+3 days'));
        } catch (\Exception) {
            $support->setDateHeure(new \DateTime('+3 days'));
        }

        $this->em->persist($support);
        $this->em->flush();

        $dateHeure = $support->getDateHeure();
        return new JsonResponse([
            'success' => true,
            'date'    => $dateHeure !== null ? $dateHeure->format('d/m/Y H:i') : 'N/A',
        ]);
    }

    #[Route('/predict', name: 'app_risk_predict', methods: ['POST'])]
    public function predict(Request $request): JsonResponse
    {
        $statut      = $request->request->get('statut', 'PLANIFIEE');
        $agitation   = $request->request->get('niveauAgitation', 'CALME');
        $duree       = (int) $request->request->get('duree', 60);
        
        $objectifRaw = $request->request->get('objectifSeance', '');
        $notesRaw    = $request->request->get('notes', '');
        $techRaw     = $request->request->get('techniqueUtilisee', '');
        $emailRaw    = $request->request->get('parentEmail', '');
        $telRaw      = $request->request->get('telephoneResponsable', '');
        
        $hasObjectif = (bool) trim(is_string($objectifRaw) ? $objectifRaw : '');
        $hasNotes   = (bool) trim(is_string($notesRaw) ? $notesRaw : '');
        $hasTech    = (bool) trim(is_string($techRaw) ? $techRaw : '');
        $hasEmail   = (bool) trim(is_string($emailRaw) ? $emailRaw : '');
        $hasTel     = (bool) trim(is_string($telRaw) ? $telRaw : '');

        $statutScore    = match($statut) { 'ANNULEE' => 100, 'PLANIFIEE' => 60, 'EN_COURS' => 20, 'TERMINEE' => 0, default => 50 };
        $agitationScore = match($agitation) { 'CRISE' => 90, 'AGITE' => 50, 'CALME' => 5, default => 30 };
        $dureeScore     = match(true) { $duree <= 0 => 80, $duree < 20 => 70, $duree < 30 => 40, $duree <= 90 => 5, $duree <= 120 => 20, default => 50 };
        $filled         = (int)$hasObjectif + (int)$hasNotes + (int)$hasTech + (int)$hasEmail + (int)$hasTel;
        $completude     = match(true) { $filled >= 4 => 0, $filled === 3 => 20, $filled === 2 => 45, $filled === 1 => 70, default => 90 };

        $score = round($statutScore * 0.30 + $agitationScore * 0.30 + $dureeScore * 0.20 + $completude * 0.20, 1);
        $level = match(true) {
            $score >= 80 => ['label' => 'CRITIQUE',   'color' => 'red',    'icon' => 'fa-exclamation-triangle'],
            $score >= 65 => ['label' => 'TRÈS ÉLEVÉ', 'color' => 'orange', 'icon' => 'fa-exclamation-triangle'],
            $score >= 45 => ['label' => 'ÉLEVÉ',      'color' => 'amber',  'icon' => 'fa-exclamation-circle'],
            $score >= 25 => ['label' => 'MODÉRÉ',     'color' => 'yellow', 'icon' => 'fa-info-circle'],
            default      => ['label' => 'FAIBLE',     'color' => 'green',  'icon' => 'fa-check-circle'],
        };

        $tips = [];
        if ($statut === 'ANNULEE')      $tips[] = '❌ Session annulée — risque très élevé';
        if ($agitation === 'CRISE')     $tips[] = '😭 Niveau de crise — adapter la technique';
        if ($agitation === 'AGITE')     $tips[] = '😰 Enfant agité — prévoir un espace calme';
        if ($duree < 20 && $duree > 0)  $tips[] = '⏱️ Séance trop courte';
        if (!$hasObjectif)              $tips[] = '🎯 Aucun objectif défini';
        if (!$hasEmail && !$hasTel)     $tips[] = '📞 Aucun contact responsable';
        if (empty($tips))               $tips[] = '✅ Profil favorable';

        return new JsonResponse([
            'score'   => $score,
            'level'   => $level,
            'factors' => ['Statut' => $statutScore, 'Agitation' => $agitationScore, 'Durée' => $dureeScore, 'Complétude' => $completude],
            'tips'    => $tips,
        ]);
    }

    #[Route('/chat', name: 'app_risk_chat', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        $messageRaw = $request->request->get('message', '');
        $message   = strtolower(trim(is_string($messageRaw) ? $messageRaw : ''));
        $riskScore = (float) $request->request->get('riskScore', 0);
        $sessionId = (int) $request->request->get('sessionId', 0);

        $intro = match(true) {
            $riskScore >= 75 => "🚨 **Situation critique** ({$riskScore}%) — ",
            $riskScore >= 50 => "🔶 **Risque élevé** ({$riskScore}%) — ",
            default          => "🟡 **Risque modéré** ({$riskScore}%) — ",
        };

        $rules = [
            ['kw' => ['abandon', 'arrêter', 'quitter'], 'r' => $intro . "L'abandon est souvent lié à la stagnation.\n\n• 📅 Session de soutien dans les 48h\n• 💬 Réajuster les objectifs\n• 📞 Contacter le responsable"],
            ['kw' => ['annulation', 'absent'],          'r' => $intro . "Les annulations répétées sont le signal le plus fort.\n\n• 📞 Appel immédiat\n• 🔔 Rappels SMS\n• 📅 Créneau de remplacement"],
            ['kw' => ['progression', 'stagnation'],     'r' => $intro . "La stagnation décourage.\n\n• 📊 Revoir les objectifs\n• 🏆 Valoriser les petits progrès\n• 🔄 Changer de technique"],
            ['kw' => ['agitation', 'crise'],            'r' => $intro . "L'agitation épuise la famille.\n\n• 🆘 Adapter la technique\n• 🏥 Consultation médicale\n• 📅 Session dans les 24h"],
            ['kw' => ['que faire', 'aide', 'conseil'],  'r' => $intro . ($riskScore >= 75 ? "🚨 Contact immédiat\n• Session urgente\n• Réunion parents" : "🔶 Contact 48h\n• Session planifiée\n• Révision objectifs")],
        ];

        foreach ($rules as $rule) {
            foreach ($rule['kw'] as $kw) {
                if (str_contains($message, $kw)) return new JsonResponse(['reply' => $rule['r']]);
            }
        }

        return new JsonResponse(['reply' => $intro . "Session #{$sessionId} — Je peux conseiller sur :\n• 📅 Session de soutien\n• 📊 Facteurs de risque\n• 💬 Stratégies de réengagement"]);
    }
}
