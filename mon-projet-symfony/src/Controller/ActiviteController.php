<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Entity\JeuEducatif;
use App\Form\ActiviteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ActiviteController extends AbstractController
{
    // ✅ LISTE
    #[Route('/admin/activite/{id}', name: 'activite_list')]
    public function list(JeuEducatif $jeu): Response
    {
        return $this->render('admin/activite_list.html.twig', [
            'jeu' => $jeu,
            'activites' => $jeu->getActivites()
        ]);
    }

 #[Route('/admin/activite/new/{id}', name: 'activite_new')]
public function new($id, Request $request, EntityManagerInterface $em): Response
{
    $jeu = $em->getRepository(JeuEducatif::class)->find($id);

    $activite = new Activite();

    $form = $this->createForm(ActiviteType::class, $activite, [
        'type_jeu' => $jeu->getType()
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $type = strtolower($jeu->getType());

  if ($type === 'intrus') {

    for ($i = 1; $i <= 4; $i++) {

        if ($form->has('image'.$i)) {

            $file = $form->get('image'.$i)->getData();

            if ($file !== null) {

                $filename = uniqid().'.'.$file->guessExtension();

                $file->move(
                    $this->getParameter('uploads_directory'),
                    $filename
                );

                switch ($i) {
                    case 1: $activite->setImage1($filename); break;
                    case 2: $activite->setImage2($filename); break;
                    case 3: $activite->setImage3($filename); break;
                    case 4: $activite->setImage4($filename); break;
                }
            }
        }
    }
}

        // 🧩 autres types
        if ($form->has('imageFile')) {
            $file = $form->get('imageFile')->getData();

            if ($file) {
                $filename = uniqid().'.'.$file->guessExtension();

                $file->move(
                    $this->getParameter('uploads_directory'),
                    $filename
                );

                $activite->setImage($filename);
            }
        }

        $activite->setJeu($jeu);

        $em->persist($activite);
        $em->flush();

        return $this->redirectToRoute('activite_list', [
            'id' => $jeu->getId()
        ]);
    }

    return $this->render('admin/activite_form.html.twig', [
        'form' => $form->createView(),
        'jeu' => $jeu
    ]);
}

    // ✅ EDIT
    #[Route('/admin/activite/edit/{id}', name: 'activite_edit')]
    public function edit(Activite $activite, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ActiviteType::class, $activite, [
            'type_jeu' => $activite->getJeu()->getType()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = null;

            if ($form->has('imageFile')) {
                $imageFile = $form->get('imageFile')->getData();
            }

            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                $imageFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );

                $activite->setImage($newFilename);
            }

            $em->flush();

            return $this->redirectToRoute('activite_list', [
                'id' => $activite->getJeu()->getId()
            ]);
        }

        return $this->render('admin/activite_form.html.twig', [
            'form' => $form->createView(),
            'jeu' => $activite->getJeu()
        ]);
    }

    // ✅ DELETE
    #[Route('/admin/activite/delete/{id}', name: 'activite_delete')]
    public function delete(Activite $activite, EntityManagerInterface $em): Response
    {
        $jeuId = $activite->getJeu()->getId();

        $em->remove($activite);
        $em->flush();

        return $this->redirectToRoute('activite_list', ['id' => $jeuId]);
    }
}