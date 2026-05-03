<?php
// src/Controller/SessionController.php
namespace App\Controller;

use App\Entity\Session;
use App\Form\SessionType;
use App\Repository\SessionRepository;
use App\Repository\SuiviProgressionRepository;
use App\Service\TwilioService;
use App\Service\EmailRappelService;
use App\Service\GoogleCalendarService;
use App\Service\GoogleMeetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/session', name: 'app_session_')]
class SessionController extends AbstractController
{
    // LISTE
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, SessionRepository $repository): Response
    {
        /** @var array<string, string> $filters */
        $filters = array_filter([
            'search'   => $request->query->get('search'),
            'dureeMin' => $request->query->get('dureeMin'),
            'dureeMax' => $request->query->get('dureeMax'),
            'favori'   => $request->query->get('favori'),
        ]);

        $page      = max(1, (int) $request->query->get('page', 1));
        $limit     = (int) $request->query->get('limit', 10);
        $sortBy    = (string)($request->query->get('sortBy') ?: 'dateHeure');
        $sortOrder = strtoupper((string)($request->query->get('sortOrder') ?: 'DESC'));

        $sessions   = $repository->findByFilters($filters, $sortBy, $sortOrder, $page, $limit);
        $totalItems = $repository->countByFilters($filters);
        $totalPages = (int) ceil($totalItems / $limit);

        return $this->render('session/index.html.twig', compact(
            'sessions', 'filters', 'totalItems', 'page', 'totalPages', 'limit', 'sortBy', 'sortOrder'
        ));
    }

    // NOUVELLE
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, GoogleCalendarService $calendar, GoogleMeetService $meetService, EmailRappelService $emailService): Response
    {
        $session = new Session();
        $form    = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // Générer automatiquement le lien Jitsi Meet
            if (!$session->getMeetLink()) {
                $session->setMeetLink($meetService->generateMeetLink());
            }
            
            $em->persist($session);
            $em->flush();

            // Sync with Google Calendar
            $dateHeure = $session->getDateHeure();
            if ($dateHeure instanceof \DateTime) {
                $endDate = clone $dateHeure;
                $endDate->modify('+' . $session->getDuree() . ' minutes');
                $result = $calendar->createEvent([
                    'title'       => 'Session ' . $session->getType(),
                    'description' => $session->getObjectifSeance() ?? '',
                    'startDate'   => $dateHeure,
                    'endDate'     => $endDate,
                ]);
                if ($result['success']) {
                    if (isset($result['event']) && is_array($result['event']) && isset($result['event']['id'])) {
                        $session->setCalendarEventId((string)$result['event']['id']);
                    }
                    if (isset($result['meetLink']) && is_string($result['meetLink'])) {
                        $session->setMeetLink($result['meetLink']);
                    }
                    $em->flush();
                }
            }

            $parentEmail = $session->getParentEmail();
            if ($parentEmail !== null && $parentEmail !== '') {
                $emailService->notifyParentAboutSession($session, $parentEmail);
            }

            $this->addFlash('success', 'Session creee — Lien Meet genere et email envoye');
            return $this->redirectToRoute('app_session_index');
        }

        return $this->render('session/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // EMPLOI DU TEMPS
    #[Route('/emploi-temps', name: 'emploi_temps', methods: ['GET'])]
    public function emploiTemps(Request $request, SessionRepository $repository, GoogleCalendarService $googleService): Response
    {
        $dateValue = $request->query->get('date', 'today');
        $date         = new \DateTime($dateValue ?: 'today');
        $debutSemaine = (clone $date)->modify('monday this week');
        $finSemaine   = (clone $date)->modify('sunday this week');

        $sessions     = $repository->findByDateRange($debutSemaine, $finSemaine);
        $googleEvents = [];

        try {
            $googleEvents = $googleService->getUpcomingEvents(7);
        } catch (\Exception $e) {}

        return $this->render('session/emploi_temps.html.twig', compact(
            'sessions', 'googleEvents', 'date', 'debutSemaine', 'finSemaine'
        ));
    }

    // CALENDRIER
    #[Route('/calendrier', name: 'calendrier', methods: ['GET'])]
    public function calendrier(Request $request, SessionRepository $repository): Response
    {
        $mois  = (int) $request->query->get('mois', date('n'));
        $annee = (int) $request->query->get('annee', date('Y'));

        $debut = new \DateTime("$annee-$mois-01");
        $fin   = (clone $debut)->modify('last day of this month 23:59:59');

        $sessions = $repository->findByDateRange($debut, $fin);

        return $this->render('session/calendrier.html.twig', compact('sessions', 'mois', 'annee'));
    }

    // STATS
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(SessionRepository $sessionRepo, SuiviProgressionRepository $progressionRepo): Response
    {
        return $this->render('session/stats_combined.html.twig', [
            'statsSession' => $sessionRepo->getStatistics(),
            'statsProgression' => $progressionRepo->getStatistics(),
        ]);
    }

    // HELPER MEET
    #[Route('/meet-helper', name: 'meet_helper', methods: ['GET'])]
    public function meetHelper(): Response
    {
        return $this->render('session/meet_helper.html.twig');
    }

    // SUIVI
    #[Route('/suivi', name: 'suivi', methods: ['GET'])]
    public function suivi(SessionRepository $repository): Response
    {
        return $this->render('session/suivi.html.twig', [
            'sessions' => [],
        ]);
    }

    // VOIR
    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Session $session): Response
    {
        return $this->render('session/show.html.twig', compact('session'));
    }

    // MODIFIER
    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Session $session, EntityManagerInterface $em, GoogleCalendarService $calendar): Response
    {
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            // Correction PHPStan : modify() n'existe pas sur DateTimeInterface, seulement DateTime
            $calendarId = $session->getCalendarEventId();
            if ($calendarId !== null) {
                $dateHeure = $session->getDateHeure();
                if ($dateHeure instanceof \DateTime) {
                    $endDate = clone $dateHeure;
                    $endDate->modify('+' . $session->getDuree() . ' minutes');
                    $result = $calendar->createEvent([
                        'title'       => 'Session ' . $session->getType(),
                        'description' => $session->getObjectifSeance() ?? '',
                        'startDate'   => $dateHeure,
                        'endDate'     => $endDate,
                    ]);
                    if (isset($result['success']) && $result['success'] && isset($result['event']) && is_array($result['event']) && isset($result['event']['id'])) {
                        $session->setCalendarEventId((string)$result['event']['id']);
                        $em->flush();
                    }
                }
            }

            $this->addFlash('success', 'Session modifiee');
            return $this->redirectToRoute('app_session_index');
        }

        return $this->render('session/edit.html.twig', compact('session', 'form'));
    }

    // SUPPRIMER
    #[Route('/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Session $session, EntityManagerInterface $em, GoogleCalendarService $calendar): Response
    {
        // Correction PHPStan : getString() retourne string (pas mixed)
        if ($this->isCsrfTokenValid('delete' . $session->getId(), $request->request->getString('_token'))) {
            $calendarId = $session->getCalendarEventId();
            if ($calendarId !== null) {
                $calendar->deleteEvent($calendarId);
            }
            $em->remove($session);
            $em->flush();
            $this->addFlash('success', 'Session supprimee');
        }
        return $this->redirectToRoute('app_session_index');
    }

    // PLANIFIER
    #[Route('/{id}/planifier', name: 'planifier', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function planifier(Request $request, Session $session, EntityManagerInterface $em, TwilioService $twilio): Response
    {
        if ($this->isCsrfTokenValid('planifier' . $session->getId(), $request->request->getString('_token'))) {
            $session->setStatut('PLANIFIEE');
            $em->flush();
            
            // Envoyer SMS automatiquement
            $telephone = $session->getTelephoneResponsable();
            if ($telephone !== null && $telephone !== '') {
                $twilio->notifyParSession($telephone, $session, 'PLANIFIEE');
                $this->addFlash('success', 'Planifiee + SMS envoye');
            } else {
                $this->addFlash('success', 'Planifiee');
            }
        }
        return $this->redirectToRoute('app_session_index');
    }

    // COMMENCER
    #[Route('/{id}/commencer', name: 'commencer', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function commencer(Request $request, Session $session, EntityManagerInterface $em, TwilioService $twilio): Response
    {
        if ($this->isCsrfTokenValid('commencer' . $session->getId(), $request->request->getString('_token'))) {
            $session->setStatut('EN_COURS');
            $em->flush();
            
            // Envoyer SMS automatiquement
            $telephone = $session->getTelephoneResponsable();
            if ($telephone !== null && $telephone !== '') {
                $twilio->notifyParSession($telephone, $session, 'EN_COURS');
                $this->addFlash('success', 'En cours + SMS envoye');
            } else {
                $this->addFlash('success', 'En cours');
            }
        }
        return $this->redirectToRoute('app_session_index');
    }

    // TERMINER
    #[Route('/{id}/terminer', name: 'terminer', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function terminer(Request $request, Session $session, EntityManagerInterface $em, TwilioService $twilio): Response
    {
        if ($this->isCsrfTokenValid('terminer' . $session->getId(), $request->request->getString('_token'))) {
            $session->setStatut('TERMINEE');
            $em->flush();
            
            // Envoyer SMS automatiquement
            $telephone = $session->getTelephoneResponsable();
            if ($telephone !== null && $telephone !== '') {
                $twilio->notifyParSession($telephone, $session, 'TERMINEE');
                $this->addFlash('success', 'Terminee + SMS envoye');
            } else {
                $this->addFlash('success', 'Terminee');
            }
        }
        return $this->redirectToRoute('app_session_index');
    }

    // EMAIL RAPPEL
    #[Route('/{id}/email-rappel', name: 'email_rappel', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function emailRappel(Session $session, EmailRappelService $emailService, GoogleMeetService $meetService, GoogleCalendarService $calendar, EntityManagerInterface $em): Response
    {
        try {
            if (!$session->getMeetLink()) {
                $session->setMeetLink($meetService->generateMeetLink());
                $em->flush();
            }

            $recipient = $session->getParentEmail();
            if ($recipient === null || $recipient === '') {
                $this->addFlash('warning', 'Aucun email parent defini');
                return $this->redirectToRoute('app_session_index');
            }

            $result = $emailService->notifyParentAboutSession($session, $recipient);
            $this->addFlash(
                $result['success'] ? 'success' : 'warning',
                $result['success'] ? 'Email envoye avec lien Meet' : 'Echec envoi email'
            );
        } catch (\Exception $e) {
            $this->addFlash('warning', 'Email echoue : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_session_index');
    }

    // NOTIFICATION SMS
    #[Route('/{id}/notify-sms', name: 'notify_sms', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function notifySms(Session $session, TwilioService $twilio): Response
    {
        try {
            $telephone = $session->getTelephoneResponsable();
            if ($telephone === null || $telephone === '') {
                $this->addFlash('warning', 'Aucun numero de telephone defini');
                return $this->redirectToRoute('app_session_index');
            }

            // Correction PHPStan : getStatut() retourne maintenant string (non-nullable)
            $statut = $session->getStatut();
            $result = $twilio->notifyParSession($telephone, $session, $statut);

            $this->addFlash(
                $result['success'] ? 'success' : 'error',
                $result['success'] ? 'SMS envoye' : 'Echec envoi SMS'
            );
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur SMS : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_session_index');
    }

    // ANNULER
    #[Route('/{id}/annuler', name: 'annuler', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function annuler(Request $request, Session $session, EntityManagerInterface $em, TwilioService $twilio, GoogleCalendarService $calendar): Response
    {
        if ($this->isCsrfTokenValid('annuler' . $session->getId(), $request->request->getString('_token'))) {
            $session->setStatut('ANNULEE');
            $em->flush();

            $calendarId = $session->getCalendarEventId();
            if ($calendarId !== null) {
                $calendar->deleteEvent($calendarId);
            }

            $telephone = $session->getTelephoneResponsable();
            if ($telephone !== null && $telephone !== '') {
                $twilio->notifyParSession($telephone, $session, 'ANNULEE');
                $this->addFlash('success', 'Session annulee + SMS envoye');
            } else {
                $this->addFlash('success', 'Session annulee');
            }
        }

        return $this->redirectToRoute('app_session_index');
    }

    // TOGGLE FAVORI
    #[Route('/{id}/favori', name: 'favori', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggleFavori(Session $session, EntityManagerInterface $em): JsonResponse
    {
        $session->setFavori(!$session->isFavori());
        $em->flush();

        return new JsonResponse([
            'favori' => $session->isFavori(),
            'id'     => $session->getId(),
        ]);
    }

    // SCANNER QR CODE
    #[Route('/scan-qr', name: 'scan_qr', methods: ['GET'])]
    public function scanQr(): Response
    {
        return $this->render('session/scan_qr.html.twig');
    }

    // CONFIRMER PRESENCE VIA QR CODE
    #[Route('/presence/{token}', name: 'confirm_presence', methods: ['GET'])]
    public function confirmPresence(string $token, SessionRepository $repository, EntityManagerInterface $em): Response
    {
        $session = $repository->findOneBy(['qrToken' => $token]);
        
        if (!$session) {
            return $this->render('session/presence_error.html.twig', [
                'error' => 'QR Code invalide ou expiré'
            ]);
        }
        
        if ($session->isPresenceConfirmee()) {
            return $this->render('session/presence_already.html.twig', [
                'session' => $session
            ]);
        }
        
        // Confirmer la présence
        $session->setPresenceConfirmeeAt(new \DateTime());
        $session->setPointsPresence($session->getPointsPresence() + 10);
        $em->flush();
        
        return $this->render('session/presence_success.html.twig', [
            'session' => $session,
            'points' => 10
        ]);
    }

    // API
    #[Route('/{id}/api', name: 'api', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function api(Session $session): JsonResponse
    {
        return new JsonResponse([
            'id'    => $session->getId(),
            'title' => 'Session #' . $session->getId(),
            'date'  => $session->getDateHeure()?->format('c'),
        ]);
    }
}
