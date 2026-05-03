<?php
// src/Controller/DashboardController.php
namespace App\Controller;

use App\Repository\SessionRepository;
use App\Repository\SuiviProgressionRepository;
use App\Repository\NotificationRepository;
use App\Service\EmailRappelService;
use App\Service\GoogleCalendarService;
use App\Service\TwilioService;
use App\Service\RisqueAbandonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use DateTime;

class DashboardController extends AbstractController
{
    public function __construct(
        private SessionRepository          $sessionRepo,
        private SuiviProgressionRepository $suiviRepo,
        private NotificationRepository     $notificationRepo,
        private TwilioService              $smsService,
        private GoogleCalendarService      $calendarService,
        private RisqueAbandonService       $predictionService,
    ) {}

    #[Route('/admin', name: 'app_dashboard_admin')]
    public function admin(): Response
    {
        $sessions = $this->sessionRepo->findBy([], ['dateHeure' => 'DESC']);
        $suivis   = $this->suiviRepo->findAll();
        $now = new DateTime();

        $sessionsToday = array_filter($sessions, fn($s) =>
            $s->getDateHeure() &&
            $s->getDateHeure()->format('Y-m-d') === $now->format('Y-m-d')
        );

        $prochaines = array_filter($sessions, fn($s) =>
            $s->getDateHeure() && $s->getDateHeure() > $now
        );
        usort($prochaines, fn($a, $b) => $a->getDateHeure() <=> $b->getDateHeure());
        $prochaines = array_slice($prochaines, 0, 3);

        // Google Calendar
        $emploiGoogle = [];
        $calendarStatus = $this->calendarService->getStatus();
        if ($calendarStatus['configured']) {
            try {
                $emploiGoogle = $this->calendarService
                    ->getEmploiDuTempsSemaine(new DateTime('monday this week'));
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }

        // ===== ANALYSE RISQUE ABANDON =====
        $riskAssessments = $this->predictionService->analyser($sessions);
        $statsRisque = [
            'total'  => count($sessions),
            'normal' => count(array_filter($sessions, fn($s) => $s->getStatut() !== 'ANNULEE')),
            'risque' => count(array_filter($sessions, fn($s) => $s->getStatut() === 'ANNULEE')),
        ];
        // =====================================

        $criticalRiskNotifications = $this->notificationRepo->findCriticalRiskNotifications(10);
        $unreadNotifications = $this->notificationRepo->findUnread(5);

        $services = [
            'email' => [
                'actif' => true,
                'nom' => 'Email Gmail',
                'icone' => 'fa-envelope',
                'couleur' => 'blue',
                'description' => 'Rappels et rapports',
            ],
            'sms' => [
                'actif' => $this->smsService->isConfigured(),
                'nom' => 'SMS Twilio',
                'icone' => 'fa-sms',
                'couleur' => 'purple',
                'description' => 'Notifications SMS',
            ],
            'calendar' => [
                'actif' => $calendarStatus['configured'],
                'nom' => 'Google Calendar',
                'icone' => 'fa-calendar',
                'couleur' => 'emerald',
                'description' => 'Agenda',
            ],
            'pdf' => [
                'actif' => true,
                'nom' => 'PDF',
                'icone' => 'fa-file-pdf',
                'couleur' => 'rose',
                'description' => 'Rapports PDF',
            ],
        ];

        $stats = [
            'totalSessions' => count($sessions),
            'totalSuivis' => count($suivis),
            'sessionsAujourdhui' => count($sessionsToday),
            'sessionsPlanifiees' => count(array_filter($sessions, fn($s) => $s->getStatut() === 'PLANIFIEE')),
            'sessionsTerminees' => count(array_filter($sessions, fn($s) => $s->getStatut() === 'TERMINEE')),
            'riskNotificationsCount' => count($criticalRiskNotifications),
            'unreadNotificationsCount' => $this->notificationRepo->countUnread(),
        ];

        return $this->render('admin.html.twig', [
            'stats' => $stats,
            'services' => $services,
            'sessionsRecentes' => array_slice($sessions, 0, 6),
            'sessionsToday' => $sessionsToday,
            'prochaines' => $prochaines,
            'emploiGoogle' => $emploiGoogle,
            'riskAssessments'  => $riskAssessments,
            'statsRisque'      => $statsRisque,
            'criticalNotifications' => $criticalRiskNotifications,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }

    #[Route('/dashboard', name: 'app_dashboard_user')]
    public function user(): Response
    {
        $sessions = $this->sessionRepo->findBy([], ['dateHeure' => 'DESC'], 20);
        $suivis   = $this->suiviRepo->findBy([], ['dateEvaluation' => 'DESC'], 10);
        $now = new DateTime();

        $prochaines = array_filter($sessions, fn($s) =>
            $s->getDateHeure() && $s->getDateHeure() > $now
        );
        usort($prochaines, fn($a, $b) => $a->getDateHeure() <=> $b->getDateHeure());
        $prochaines = array_slice($prochaines, 0, 5);

        // Analyse risque pour user dashboard
        $riskAssessments = $this->predictionService->analyser($sessions);

        $unreadNotifications = $this->notificationRepo->findUnread(5);

        $stats = [
            'totalSessions' => count($sessions),
            'totalSuivis' => count($suivis),
            'sessionsPlanifiees' => count(array_filter($sessions, fn($s) => $s->getStatut() === 'PLANIFIEE')),
            'sessionsTerminees' => count(array_filter($sessions, fn($s) => $s->getStatut() === 'TERMINEE')),
            'unreadNotificationsCount' => $this->notificationRepo->countUnread(),
        ];

        return $this->render('user.html.twig', [
            'stats'            => $stats,
            'services'         => [
                'email'    => ['actif' => true,                          'nom' => 'Email Gmail',     'icone' => 'fa-envelope', 'couleur' => 'blue',    'description' => 'Rappels et rapports'],
                'sms'      => ['actif' => $this->smsService->isConfigured(), 'nom' => 'SMS Twilio',  'icone' => 'fa-sms',      'couleur' => 'purple',  'description' => 'Notifications SMS'],
                'calendar' => ['actif' => $this->calendarService->getStatus()['configured'], 'nom' => 'Google Calendar', 'icone' => 'fa-calendar', 'couleur' => 'emerald', 'description' => 'Agenda'],
                'pdf'      => ['actif' => true,                          'nom' => 'PDF',             'icone' => 'fa-file-pdf', 'couleur' => 'rose',    'description' => 'Rapports PDF'],
            ],
            'sessionsRecentes' => array_slice($sessions, 0, 6),
            'prochaines'       => $prochaines,
            'suivisRecents'    => array_slice($suivis, 0, 5),
            'riskAssessments'  => $riskAssessments,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }
}