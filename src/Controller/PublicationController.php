<?php

namespace App\Controller;

use App\Entity\Publication;
use App\Entity\PublicationHasLanguage;
use App\Form\PublicationType;
use App\Form\PublicationHasLanguageType;
use App\Repository\PublicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/publication')]
#[IsGranted('ROLE_LIBRARIAN')]
class PublicationController extends AbstractController
{
    #[Route('/', name: 'app_publication_index', methods: ['GET'])]
    public function index(PublicationRepository $publicationRepository): Response
    {
        return $this->render('publication/index.html.twig', [
            'publications' => $publicationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_publication_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $publication = new Publication();
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($publication);
            $entityManager->flush();

            $this->addFlash('success', 'Publication base details created successfully. Now add language editions.');

            return $this->redirectToRoute('app_publication_add_language', [
                'id' => $publication->getId()
            ]);
        }

        return $this->render('publication/new.html.twig', [
            'publication' => $publication,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/add-language', name: 'app_publication_add_language', methods: ['GET', 'POST'])]
    public function addLanguage(Request $request, Publication $publication, EntityManagerInterface $entityManager): Response
    {
        $publicationHasLanguage = new PublicationHasLanguage();
        $publicationHasLanguage->setPublication($publication);

        $form = $this->createForm(PublicationHasLanguageType::class, $publicationHasLanguage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($publicationHasLanguage);
            $entityManager->flush();

            $this->addFlash('success', 'Language edition added successfully');

            if ($request->request->has('add_another')) {
                return $this->redirectToRoute('app_publication_add_language', [
                    'id' => $publication->getId()
                ]);
            } else {
                return $this->redirectToRoute('app_publication_show', [
                    'id' => $publication->getId()
                ]);
            }
        }

        return $this->render('publication/add_language.html.twig', [
            'publication' => $publication,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_publication_show', methods: ['GET'])]
    public function show(Publication $publication): Response
    {
        return $this->render('publication/show.html.twig', [
            'publication' => $publication,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_publication_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Publication $publication, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Publication updated successfully');
            return $this->redirectToRoute('app_publication_show', ['id' => $publication->getId()]);
        }

        return $this->render('publication/edit.html.twig', [
            'publication' => $publication,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_publication_delete', methods: ['POST'])]
    public function delete(Request $request, Publication $publication, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$publication->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($publication);
            $entityManager->flush();
            $this->addFlash('success', 'Publication deleted successfully');
        }

        return $this->redirectToRoute('app_publication_index');
    }
}