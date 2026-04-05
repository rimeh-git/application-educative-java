<?php

namespace App\Controller;

use App\Entity\JeuEducatif;
use App\Entity\Activite;
use App\Repository\JeuEducatifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\JeuEducatifType;
   use Knp\Component\Pager\PaginatorInterface;

class JeuController extends AbstractController
{

#[Route('/jeu/play', name: 'play_game')]
public function play(Request $request, JeuEducatifRepository $repo, PaginatorInterface $paginator)
{
    $search = $request->query->get('search', '');
    $direction = $request->query->get('direction', '');

    $query = $repo->createQueryBuilder('j');

    if ($search) {
        $query->andWhere('j.type LIKE :search')
              ->setParameter('search', "%$search%");
    }

    if ($direction === 'az') {
        $query->orderBy('j.type', 'ASC');
    } elseif ($direction === 'za') {
        $query->orderBy('j.type', 'DESC');
    }

    $pagination = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        4
    );

    return $this->render('jeu/play.html.twig', [
        'pagination' => $pagination,
        'search' => $search,
        'direction' => $direction // ✅ IMPORTANT
    ]);
}
    #[Route('/jeu/play/{id}', name: 'play_one_game')]
public function playOneGame(JeuEducatif $jeu): Response
{
    return $this->redirectToRoute('jeu_play', [
        'id' => $jeu->getId(),
        'niveau' => 1
    ]);
}

    #[Route('/jeu/levels/{id}', name: 'jeu_levels')]
    public function levels(JeuEducatif $jeu): Response
    {
        if ($jeu->getActivites()->isEmpty()) {
            return new Response("❌ Aucun niveau disponible");
        }

        return $this->render('jeu/levels.html.twig', [
            'jeu' => $jeu
        ]);
    }

    #[Route('/jeu/play/{id}/niveau/{niveau}', name: 'jeu_play')]
    public function playByNiveau($id, $niveau, Request $request, EntityManagerInterface $em): Response
    {
        $activite = $em->getRepository(Activite::class)->findOneBy([
            'jeu' => $id,
            'niveau' => $niveau
        ]);

        if (!$activite) {
            throw $this->createNotFoundException('Activité non trouvée');
        }

        $type = strtolower($activite->getJeu()->getType());

        // 🧠 QUIZ
        if ($type === 'quiz') {
            $activites = $em->getRepository(Activite::class)->findBy([
                'jeu' => $id,
                'niveau' => $niveau
            ]);

            return $this->render('jeu/quiz.html.twig', [
                'activites' => $activites,
                'niveau' => $niveau
            ]);
        }

        // 🔍 INTRUS
        if ($type === 'intrus') {
    return $this->render('jeu/intrus.html.twig', [
        'activite' => $activite,
        'niveau' => $niveau // ✅ AJOUTE ÇA
    ]);
}

        // 😊 EMOTION IMAGE
       if ($type === 'emotion') {
    $activites = $em->getRepository(Activite::class)->findBy([
        'jeu' => $id,
        'niveau' => $niveau
    ]);

    return $this->render('jeu/emotion.html.twig', [
        'activites' => $activites,
        'niveau' => $niveau
    ]);
}

        // 🟢 PHRASE (ICI ✅)
        if ($type === 'emotion_phrase') {

            $reponse = $request->request->get('reponse');

            if ($reponse) {

                if (strtolower(trim($reponse)) === strtolower($activite->getBonneReponse())) {

                    return $this->redirectToRoute('jeu_play', [
                        'id' => $id,
                        'niveau' => $niveau + 1
                        
                    ]);

                } else {
                    $this->addFlash('error', '❌ Mauvaise réponse');
                }
            }

            return $this->render('jeu/emotion_phrase.html.twig', [
                'activite' => $activite
            ]);
        }

        // 🧩 PUZZLE
       return $this->render('jeu/puzzle.html.twig', [
    'jeu' => $activite->getJeu(),
    'activites' => $activite,
    'niveau' => $niveau
]);}
 #[Route('/admin/jeu/edit/{id}', name: 'jeu_edit')]
public function edit(JeuEducatif $jeu, Request $request, EntityManagerInterface $em): Response
{
    $form = $this->createForm(JeuEducatifType::class, $jeu);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $em->flush();

        $this->addFlash('success', '✅ Jeu modifié avec succès'); // ✅ corrigé

        return $this->redirectToRoute('admin_dashboard');
    }

    return $this->render('admin/jeu_form.html.twig', [
        'form' => $form->createView(),
        'jeu' => $jeu
    ]);
}
#[Route('/admin/jeu/delete/{id}', name: 'jeu_delete')]
public function delete(JeuEducatif $jeu, EntityManagerInterface $em): Response
{
    $em->remove($jeu);
    $em->flush();

    $this->addFlash('success', '🗑 Jeu supprimé avec succès');

    return $this->redirectToRoute('admin_dashboard');
}
#[Route('/admin/new', name: 'jeu_new')]
public function new(Request $request, EntityManagerInterface $em): Response
{
    $jeu = new JeuEducatif();

    if ($request->isMethod('POST')) {

        $jeu->setType($request->request->get('type'));
        $jeu->setNiveau($request->request->get('niveau'));
        $jeu->setDescription($request->request->get('description'));
        $jeu->setImage($request->request->get('image'));

        $em->persist($jeu);
        $em->flush();

        return $this->redirectToRoute('admin_dashboard');
    }

    return $this->render('admin/jeu_new.html.twig');
}
#[Route('/jeu/like/{id}', name: 'jeu_like')]
public function like(JeuEducatif $jeu, EntityManagerInterface $em): Response
{
    $jeu->setLikes($jeu->getLikes() + 1);

    $em->flush();

    return $this->json([
        'likes' => $jeu->getLikes()
    ]);
}
}