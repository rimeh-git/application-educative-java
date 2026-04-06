<?php

namespace App\Controller;

use App\Entity\SuiviProgression;
use App\Entity\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{
    #[Route('/test-create', name: 'test_create')]
    public function test(EntityManagerInterface $em): Response
    {
        // Vérifier s'il y a des sessions
        $session = $em->getRepository(Session::class)->findOneBy([]);
        
        if (!$session) {
            return new Response('❌ ERREUR: Crée d\'abord une session dans /session/new');
        }

        // Créer un suivi
        $suivi = new SuiviProgression();
        $suivi->setDomaine('COMMUNICATION');
        $suivi->setScoreAvant(3);
        $suivi->setScoreApres(7);
        $suivi->setDateEvaluation(new \DateTime());
        $suivi->setSession($session);

        $em->persist($suivi);
        $em->flush();

        return new Response('✅ SUCCES ! Suivi #' . $suivi->getId() . ' créé pour la session #' . $session->getId());
    }
}