<?php

namespace App\Controller;

use App\Entity\Publication;
use App\Entity\PublicationHasLanguage;
use App\Form\PublicationType;
use App\Form\PublicationHasLanguageType;
use App\Repository\PublicationRepository;
use App\Repository\ThemeRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/publication')]
class PublicationController extends AbstractController
{
    #[Route('/', name: 'app_publication_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, PublicationRepository $publicationRepository,
                         ThemeRepository $themeRepository, CategoryRepository $categoryRepository): Response
    {
        $search = $request->query->get('search');
        $themeId = $request->query->get('theme');
        $categoryId = $request->query->get('category');
        $titleSearch = $request->query->get('title');

        $publications = $publicationRepository->findAll();

        // Apply filters if they are set
        if (!empty($search) || !empty($themeId) || !empty($categoryId) || !empty($titleSearch)) {
            $filteredPublications = [];

            foreach ($publications as $publication) {
                $matchesSearch = true;
                $matchesTheme = true;
                $matchesCategory = true;
                $matchesTitle = true;

                // Filter by search term
                if (!empty($search)) {
                    $themeTitle = $publication->getTheme() ? strtolower($publication->getTheme()->getTitle()) : '';
                    $authorName = $publication->getAuthor() ? strtolower($publication->getAuthor()->getFullname()) : '';
                    $searchTerm = strtolower($search);

                    if (strpos($themeTitle, $searchTerm) === false && strpos($authorName, $searchTerm) === false) {
                        $matchesSearch = false;
                    }
                }

                // Filter by title
                if (!empty($titleSearch)) {
                    $titleSearchLower = strtolower($titleSearch);
                    $matchesTitle = false;

                    // Check each language edition's title
                    foreach ($publication->getPublicationHasLanguages() as $pubLang) {
                        if (strpos(strtolower($pubLang->getTitle()), $titleSearchLower) !== false) {
                            $matchesTitle = true;
                            break;
                        }
                    }
                }

                // Filter by theme
                if (!empty($themeId) && ($publication->getTheme() === null || (string)$publication->getTheme()->getId() !== (string)$themeId)) {
                    $matchesTheme = false;
                }

                // Filter by category
                if (!empty($categoryId) && ($publication->getCategory() === null || (string)$publication->getCategory()->getId() !== (string)$categoryId)) {
                    $matchesCategory = false;
                }

                // Add to filtered results if it matches all criteria
                if ($matchesSearch && $matchesTheme && $matchesCategory && $matchesTitle) {
                    $filteredPublications[] = $publication;
                }
            }

            $publications = $filteredPublications;
        }

        $themes = $themeRepository->findAll();
        $categories = $categoryRepository->findAll();

        return $this->render('publication/index.html.twig', [
            'publications' => $publications,
            'themes' => $themes,
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'app_publication_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_LIBRARIAN')]
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
    #[IsGranted('ROLE_LIBRARIAN')]
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
    #[IsGranted('ROLE_USER')]
    public function show(Publication $publication, EntityManagerInterface $entityManager): Response
    {
        // For each language edition, eager load all ratings
        foreach ($publication->getPublicationHasLanguages() as $edition) {
            $edition->getRatings()->initialize();
        }

        return $this->render('publication/show.html.twig', [
            'publication' => $publication,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_publication_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_LIBRARIAN')]
    public function edit(Request $request, Publication $publication, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);

        // Create delete form
        $form_delete = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_publication_delete', ['id' => $publication->getId()]))
            ->setMethod('DELETE')
            ->getForm();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Publication updated successfully');
            return $this->redirectToRoute('app_publication_show', ['id' => $publication->getId()]);
        }

        return $this->render('publication/edit.html.twig', [
            'publication' => $publication,
            'form' => $form->createView(),
            'form_delete' => $form_delete->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_publication_delete', methods: ['POST', 'DELETE'])]
    #[IsGranted('ROLE_LIBRARIAN')]
    public function delete(Request $request, Publication $publication, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$publication->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($publication);
            $entityManager->flush();
            $this->addFlash('success', 'Publication deleted successfully');
        }

        return $this->redirectToRoute('app_publication_index');
    }

    #[Route('/{id}/language/{langId}/edit', name: 'app_publication_language_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_LIBRARIAN')]
    public function editLanguage(Request $request, Publication $publication, int $langId, EntityManagerInterface $entityManager): Response
    {
        // Find the specific language edition
        $publicationHasLanguage = $entityManager->getRepository(PublicationHasLanguage::class)->find($langId);

        if (!$publicationHasLanguage || $publicationHasLanguage->getPublication()->getId() !== $publication->getId()) {
            throw $this->createNotFoundException('Language edition not found for this publication');
        }

        $form = $this->createForm(PublicationHasLanguageType::class, $publicationHasLanguage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Language edition updated successfully');
            return $this->redirectToRoute('app_publication_edit', ['id' => $publication->getId()]);
        }

        return $this->render('publication/edit_language.html.twig', [
            'publication' => $publication,
            'language_edition' => $publicationHasLanguage,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/language/{langId}/delete', name: 'app_publication_language_delete', methods: ['POST', 'DELETE'])]
    #[IsGranted('ROLE_LIBRARIAN')]
    public function deleteLanguage(Request $request, Publication $publication, int $langId, EntityManagerInterface $entityManager): Response
    {
        $publicationHasLanguage = $entityManager->getRepository(PublicationHasLanguage::class)->find($langId);

        if (!$publicationHasLanguage || $publicationHasLanguage->getPublication()->getId() !== $publication->getId()) {
            throw $this->createNotFoundException('Language edition not found for this publication');
        }

        if ($this->isCsrfTokenValid('delete-language'.$langId, $request->getPayload()->getString('_token'))) {
            $entityManager->remove($publicationHasLanguage);
            $entityManager->flush();
            $this->addFlash('success', 'Language edition deleted successfully');
        }

        return $this->redirectToRoute('app_publication_edit', ['id' => $publication->getId()]);
    }
}