<?php

namespace App\Controller;

use App\Entity\Rating;
use App\Entity\Reservation;
use App\Entity\PublicationHasLanguage;
use App\Repository\RatingRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/rating')]
class RatingController extends AbstractController
{
    /**
     * Check if a user has reserved a specific publication
     */
    private function hasUserReservedPublication(int $userId, int $publicationId, ReservationRepository $reservationRepository): bool
    {
        return $reservationRepository->hasUserReservedPublication($userId, $publicationId);
    }

    #[Route('/new/{id}', name: 'app_rating_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request,
        PublicationHasLanguage $publicationHasLanguage,
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
        RatingRepository $ratingRepository
    ): Response {
        // Check if user has reserved this publication
        $user = $this->getUser();
        if (!$this->hasUserReservedPublication($user->getId(), $publicationHasLanguage->getId(), $reservationRepository)) {
            $this->addFlash('error', 'You can only rate and comment on books you have reserved.');
            return $this->redirectToRoute('app_publication_show', ['id' => $publicationHasLanguage->getPublication()->getId()]);
        }

        // Check if user has already rated this publication
        $existingRating = $ratingRepository->findByUserAndPublicationLanguage($user, $publicationHasLanguage);

        if ($existingRating) {
            $rating = $existingRating;
            $newRating = false;
        } else {
            $rating = new Rating();
            $rating->setUser($user);
            $rating->setPublicationHasLanguage($publicationHasLanguage);
            $newRating = true;
        }

        if ($request->isMethod('POST')) {
            $comment = $request->request->get('comment');
            $ratingValue = (int) $request->request->get('rating');

            // Validate rating
            if ($ratingValue < 1 || $ratingValue > 5) {
                $this->addFlash('error', 'Rating must be between 1 and 5.');
                return $this->redirectToRoute('app_rating_new', ['id' => $publicationHasLanguage->getId()]);
            }

            $rating->setComment($comment);
            $rating->setRating($ratingValue);
            $rating->setActive(true); // Set as active by default

            if ($newRating) {
                $entityManager->persist($rating);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Your rating and comment have been saved.');
            return $this->redirectToRoute('app_publication_show', ['id' => $publicationHasLanguage->getPublication()->getId()]);
        }

        return $this->render('rating/new.html.twig', [
            'publication' => $publicationHasLanguage,
            'rating' => $rating,
            'edit_mode' => !$newRating
        ]);
    }

    #[Route('/{id}', name: 'app_rating_show', methods: ['GET'])]
    public function show(PublicationHasLanguage $publicationHasLanguage, RatingRepository $ratingRepository): Response
    {
        // For admins, show all ratings (active and inactive)
        if ($this->isGranted('ROLE_ADMIN')) {
            $ratings = $ratingRepository->findBy(['publicationHasLanguage' => $publicationHasLanguage], ['id' => 'DESC']);
        } else {
            // For regular users, only show active ratings
            $ratings = $ratingRepository->findBy(
                ['publicationHasLanguage' => $publicationHasLanguage, 'active' => true],
                ['id' => 'DESC']
            );
        }

        return $this->render('rating/show.html.twig', [
            'publication' => $publicationHasLanguage,
            'ratings' => $ratings
        ]);
    }

    #[Route('/{id}/toggle-status', name: 'app_rating_toggle_status', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function toggleStatus(Rating $rating, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Toggle the active status
        $rating->setActive(!$rating->isActive());
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Review ' . ($rating->isActive() ? 'approved' : 'hidden') . ' successfully'
        );

        // Get the referer to redirect back to the page the user was on
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_rating_show', [
            'id' => $rating->getPublicationHasLanguage()->getId()
        ]));
    }
}