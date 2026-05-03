<?php

namespace App\Controller;

use App\Entity\MoodTracking;
use App\Entity\Patient;
use App\Repository\MoodTrackingRepository;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mood')]
class MoodController extends AbstractController
{
    #[Route('/{patientId}', name: 'mood_select', requirements: ['patientId' => '\d+'])]
    public function select(int $patientId, PatientRepository $patientRepo): Response
    {
        $patient = $patientRepo->find($patientId);
        if (!$patient) {
            throw $this->createNotFoundException('Patient non trouvé');
        }

        return $this->render('mood/select.html.twig', [
            'patient' => $patient,
        ]);
    }

    #[Route('/save', name: 'mood_save', methods: ['POST'])]
    public function save(Request $request, EntityManagerInterface $em, PatientRepository $patientRepo): Response
    {
        $patientId = $request->request->get('patient_id');
        $mood = $request->request->get('mood');

        $patient = $patientRepo->find($patientId);
        if (!$patient || !in_array($mood, ['happy', 'neutral', 'sad'])) {
            return $this->json(['success' => false], 400);
        }

        $moodTracking = new MoodTracking();
        $moodTracking->setPatient($patient);
        $moodTracking->setMood($mood);

        $em->persist($moodTracking);
        $em->flush();

        return $this->render('mood/success.html.twig', [
            'mood' => $mood,
            'patient' => $patient,
        ]);
    }

    #[Route('/dashboard', name: 'mood_dashboard')]
    public function dashboard(MoodTrackingRepository $moodRepo, PatientRepository $patientRepo): Response
    {
        $stats = $moodRepo->getMoodStats();
        $recentMoods = $moodRepo->findBy([], ['createdAt' => 'DESC'], 50);

        return $this->render('mood/dashboard.html.twig', [
            'stats' => $stats,
            'recentMoods' => $recentMoods,
        ]);
    }

    #[Route('/qr-codes', name: 'mood_qr_codes')]
    public function qrCodes(PatientRepository $patientRepo): Response
    {
        $patients = $patientRepo->findAll();

        return $this->render('mood/qr_codes.html.twig', [
            'patients' => $patients,
        ]);
    }

    #[Route('/test', name: 'mood_test')]
    public function test(PatientRepository $patientRepo): Response
    {
        $firstPatient = $patientRepo->findOneBy([]);

        return $this->render('mood/test.html.twig', [
            'firstPatient' => $firstPatient,
        ]);
    }
}
