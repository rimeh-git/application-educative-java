<?php

namespace App\Controller;

use App\Entity\JeuEducatif;
use App\Form\JeuType;
use App\Repository\JeuEducatifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(JeuEducatifRepository $repo)
    {
        return $this->render('admin/index.html.twig', [
            'jeux' => $repo->findAll()
        ]);
    }

    #[Route('/admin/new', name: 'admin_new')]
    public function new(Request $request, EntityManagerInterface $em)
    {
        $jeu = new JeuEducatif();
        $form = $this->createForm(JeuType::class, $jeu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('imageFile')->getData();

            if ($file) {
                $filename = uniqid().'.'.$file->guessExtension();
                $file->move($this->getParameter('uploads_directory'), $filename);
                $jeu->setImage($filename);
            }

            $em->persist($jeu);
            $em->flush();

            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/edit/{id}', name: 'admin_edit')]
    public function edit(JeuEducatif $jeu, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(JeuType::class, $jeu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('imageFile')->getData();

            if ($file) {
                $filename = uniqid().'.'.$file->guessExtension();
                $file->move($this->getParameter('uploads_directory'), $filename);
                $jeu->setImage($filename);
            }

            $em->flush();

            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/edit.html.twig', [
            'form' => $form->createView(),
            'jeu' => $jeu
        ]);
    }

   #[Route('/admin/delete/{id}', name: 'admin_delete')]
public function delete($id, JeuEducatifRepository $repo, EntityManagerInterface $em)
{
    $jeu = $repo->find($id);

    if (!$jeu) {
        $this->addFlash('error', 'Jeu introuvable');
        return $this->redirectToRoute('admin_dashboard');
    }

    $em->remove($jeu);
    $em->flush();

    return $this->redirectToRoute('admin_dashboard');
}
}