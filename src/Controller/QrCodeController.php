<?php

namespace App\Controller;

use App\Entity\Session;
use App\Repository\SessionRepository;
use App\Service\QrGamificationService;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/qr')]
class QrCodeController extends AbstractController
{
    public function __construct(
        private readonly QrGamificationService $gamification,
    ) {}

    // ── Génère le QR Code SVG ──
    #[Route('/session/{id}/image', name: 'app_qr_image')]
    public function image(Session $session, EntityManagerInterface $em): Response
    {
        if (!$session->getQrToken()) {
            $session->setQrToken(bin2hex(random_bytes(16)));
            $em->flush();
        }

        $url    = $this->generateUrl('app_qr_confirm', ['token' => $session->getQrToken()], UrlGeneratorInterface::ABSOLUTE_URL);
        $result = (new SvgWriter())->write(new QrCode($url));

        return new Response($result->getString(), 200, ['Content-Type' => 'image/svg+xml']);
    }

    // ── Confirmation après scan ──
    #[Route('/confirm/{token}', name: 'app_qr_confirm')]
    public function confirm(string $token, SessionRepository $repo, EntityManagerInterface $em): Response
    {
        $session = $repo->findOneBy(['qrToken' => $token]);

        if (!$session) {
            return $this->render('qr/invalid.html.twig');
        }

        $dejaConfirmee = $session->isPresenceConfirmee();
        $pointsGagnes  = 0;

        if (!$dejaConfirmee) {
            $pts = $this->gamification->getPointsPresence();
            $session->setPresenceConfirmeeAt(new \DateTime());
            $session->setPointsPresence($pts);
            $em->flush();
            $pointsGagnes = $pts;
        }

        // Calcul gamification global
        $totalPoints     = $this->gamification->getTotalPoints();
        $niveau          = $this->gamification->getNiveau($totalPoints);
        $badgesDebloques = $this->gamification->getBadgesDebloques($totalPoints);
        $prochainBadge   = $this->gamification->getProchainBadge($totalPoints);
        $nbConfirmees    = $this->gamification->getNbConfirmees();

        // Badge nouvellement débloqué lors de ce scan
        $nouveauBadge = null;
        if (!$dejaConfirmee && $pointsGagnes > 0) {
            $ancienTotal     = $totalPoints - $pointsGagnes;
            $anciensBadges   = $this->gamification->getBadgesDebloques($ancienTotal);
            $nouveauxBadges  = $this->gamification->getBadgesDebloques($totalPoints);
            if (count($nouveauxBadges) > count($anciensBadges)) {
                $nouveauBadge = end($nouveauxBadges);
            }
        }

        return $this->render('qr/confirm.html.twig', [
            'session'         => $session,
            'dejaConfirmee'   => $dejaConfirmee,
            'pointsGagnes'    => $pointsGagnes,
            'totalPoints'     => $totalPoints,
            'niveau'          => $niveau,
            'badgesDebloques' => $badgesDebloques,
            'prochainBadge'   => $prochainBadge,
            'nbConfirmees'    => $nbConfirmees,
            'nouveauBadge'    => $nouveauBadge,
        ]);
    }

    // ── Ajouter feedback thérapeute ──
    #[Route('/feedback/{id}', name: 'app_qr_feedback', methods: ['POST'])]
    public function addFeedback(Session $session, Request $request, EntityManagerInterface $em): Response
    {
        $feedback = $request->request->get('feedback');
        
        if ($feedback) {
            $session->setFeedbackTherapeute($feedback);
            $session->setFeedbackAt(new \DateTime());
            $em->flush();
            
            $this->addFlash('success', 'Feedback ajouté avec succès!');
        }
        
        return $this->redirectToRoute('app_session_show', ['id' => $session->getId()]);
    }

    // ── Reset confirmation (admin) ──
    #[Route('/session/{id}/reset', name: 'app_qr_reset', methods: ['POST'])]
    public function reset(Session $session, EntityManagerInterface $em): Response
    {
        $session->setPresenceConfirmeeAt(null);
        $session->setPointsPresence(0);
        $em->flush();

        $this->addFlash('success', 'Confirmation reinitialisee.');
        return $this->redirectToRoute('app_session_show', ['id' => $session->getId()]);
    }

    // ── Demo : simule le scan depuis le navigateur ──
    #[Route('/demo/{id}', name: 'app_qr_demo')]
    public function demo(Session $session, EntityManagerInterface $em): Response
    {
        if (!$session->getQrToken()) {
            $session->setQrToken(bin2hex(random_bytes(16)));
            $em->flush();
        }
        return $this->redirectToRoute('app_qr_confirm', ['token' => $session->getQrToken()]);
    }
}
