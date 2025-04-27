<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\ReservationPublication;
use App\Entity\PublicationHasLanguage;
use App\Repository\ReservationRepository;
use App\Repository\PublicationHasLanguageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use \DateTime;
use App\Entity\User;

#[Route('/reservation')]
class ReservationController extends AbstractController
{
    /**
     * Calculate the total cost of a reservation
     */
    private function calculateReservationCost(Reservation $reservation): int
    {
        $totalCost = 0;
        foreach ($reservation->getReservationPublications() as $item) {
            $totalCost += $item->getPublicationHasLanguage()->getSalesPrice();
        }
        return $totalCost;
    }

    #[Route('/', name: 'app_reservation_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request, ReservationRepository $reservationRepository, EntityManagerInterface $entityManager): Response
    {
        // Get search query if any
        $searchQuery = $request->query->get('search');
        $userId = $request->query->get('user');

        // Get all reservations (default) or filter by search/user
        $reservations = [];
        $users = [];
        $userReservations = [];

        if ($userId) {
            // If specific user is selected, get only their reservations
            $user = $entityManager->getRepository(User::class)->find($userId);
            if ($user) {
                $reservations = $reservationRepository->findBy(['user' => $user], ['created_at' => 'DESC']);
                $users = [$user];
            }
        } else {
            // Get all users for the dropdown
            $userRepo = $entityManager->getRepository(User::class);

            if ($searchQuery) {
                // Search users by name or email
                $users = $userRepo->createQueryBuilder('u')
                    ->where('u.email LIKE :search')
                    ->orWhere('u.first_name LIKE :search')
                    ->orWhere('u.last_name LIKE :search')
                    ->setParameter('search', '%' . $searchQuery . '%')
                    ->getQuery()
                    ->getResult();
            } else {
                // Get all users
                $users = $userRepo->findAll();
            }

            // Get all reservations
            $reservations = $reservationRepository->findBy([], ['created_at' => 'DESC']);
        }

        // Group reservations by user
        foreach ($users as $user) {
            $userReservations[$user->getId()] = [
                'user' => $user,
                'reservations' => [],
                'totalCount' => 0,
                'totalCost' => 0
            ];
        }

        // Calculate costs and group by user
        foreach ($reservations as $reservation) {
            $userId = $reservation->getUser()->getId();

            // Initialize user data if not already set (for when loading all reservations)
            if (!isset($userReservations[$userId])) {
                $userReservations[$userId] = [
                    'user' => $reservation->getUser(),
                    'reservations' => [],
                    'totalCount' => 0,
                    'totalCost' => 0
                ];
            }

            $cost = $this->calculateReservationCost($reservation);

            $userReservations[$userId]['reservations'][] = $reservation;
            $userReservations[$userId]['totalCount']++;
            $userReservations[$userId]['totalCost'] += $cost;
        }

        // Calculate individual reservation costs for the template
        $reservationCosts = [];
        foreach ($reservations as $reservation) {
            $reservationCosts[$reservation->getId()] = $this->calculateReservationCost($reservation);
        }

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
            'is_admin_view' => true,
            'reservation_costs' => $reservationCosts,
            'user_reservations' => $userReservations,
            'selected_user_id' => $userId,
            'search_query' => $searchQuery
        ]);
    }

    #[Route('/cart', name: 'app_reservation_cart', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function cart(
        SessionInterface $session,
        PublicationHasLanguageRepository $publicationHasLanguageRepository
    ): Response
    {
        $cart = $session->get('cart', []);
        $cartItems = [];
        $returnDate = (new DateTime())->modify('+10 days');

        foreach ($cart as $id => $quantity) {
            $publication = $publicationHasLanguageRepository->find($id);
            if ($publication) {
                $cartItems[] = [
                    'publication' => $publication,
                    'quantity' => $quantity,
                    'returnDate' => $returnDate
                ];
            }
        }

        return $this->render('reservation/cart.html.twig', [
            'items' => $cartItems,
        ]);
    }

    #[Route('/add/{id}', name: 'app_reservation_add_to_cart', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addToCart(
        Request $request,
        PublicationHasLanguage $publicationHasLanguage,
        SessionInterface $session
    ): Response
    {
        // Check if the publication has enough quantity
        if ($publicationHasLanguage->getQuantity() <= 0) {
            $this->addFlash('error', 'This publication is not available for reservation.');
            return $this->redirectToRoute('app_publication_show', ['id' => $publicationHasLanguage->getPublication()->getId()]);
        }

        $cart = $session->get('cart', []);
        $id = $publicationHasLanguage->getId();

        // Initialize or increment
        if (!isset($cart[$id])) {
            $cart[$id] = 1;
        } else {
            // Check if we can add more of this publication
            if ($cart[$id] >= $publicationHasLanguage->getQuantity()) {
                $this->addFlash('error', 'You cannot reserve more copies than available.');
                return $this->redirectToRoute('app_publication_show', ['id' => $publicationHasLanguage->getPublication()->getId()]);
            }
            $cart[$id]++;
        }

        $session->set('cart', $cart);
        $this->addFlash('success', 'Publication added to your reservation cart.');

        // Get referrer from request headers or use a default
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_publication_show', ['id' => $publicationHasLanguage->getPublication()->getId()]));
    }

    #[Route('/remove/{id}', name: 'app_reservation_remove_from_cart', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function removeFromCart(
        Request $request,
        int $id,
        SessionInterface $session
    ): Response
    {
        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {
            if ($cart[$id] > 1) {
                $cart[$id]--;
            } else {
                unset($cart[$id]);
            }
        }

        $session->set('cart', $cart);
        $this->addFlash('success', 'Item removed from cart.');

        return $this->redirectToRoute('app_reservation_cart');
    }

    #[Route('/clear', name: 'app_reservation_clear_cart', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function clearCart(SessionInterface $session): Response
    {
        $session->remove('cart');
        $this->addFlash('success', 'Your reservation cart has been cleared.');

        return $this->redirectToRoute('app_reservation_cart');
    }

    #[Route('/confirm', name: 'app_reservation_confirm', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function confirmReservation(
        SessionInterface $session,
        EntityManagerInterface $entityManager,
        PublicationHasLanguageRepository $publicationHasLanguageRepository
    ): Response
    {
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            $this->addFlash('error', 'Your cart is empty. Add publications before confirming.');
            return $this->redirectToRoute('app_reservation_cart');
        }

        // Create a new reservation
        $reservation = new Reservation();
        $reservation->setUser($this->getUser());
        $reservation->setCreatedAt(new DateTime());

        $entityManager->persist($reservation);

        // Default return date (10 days from now)
        $returnDate = (new DateTime())->modify('+10 days');

        // Add each cart item to the reservation
        $allAvailable = true;
        foreach ($cart as $publicationId => $quantity) {
            $publication = $publicationHasLanguageRepository->find($publicationId);

            if (!$publication || $publication->getQuantity() < $quantity) {
                $allAvailable = false;
                $this->addFlash('error', 'Some items are no longer available in the requested quantity.');
                break;
            }

            for ($i = 0; $i < $quantity; $i++) {
                $reservationPublication = new ReservationPublication();
                $reservationPublication->setReservation($reservation);
                $reservationPublication->setPublicationHasLanguage($publication);
                $reservationPublication->setReturnDate($returnDate);

                $entityManager->persist($reservationPublication);

                // Reduce the available quantity
                $publication->setQuantity($publication->getQuantity() - 1);
            }
        }

        if ($allAvailable) {
            $entityManager->flush();
            $session->remove('cart');

            $this->addFlash('success', 'Your reservation has been confirmed successfully!');

            // Redirect to profile for regular users, admin panel for admins
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('app_reservation_index');
            } else {
                return $this->redirectToRoute('app_profile_index');
            }
        } else {
            return $this->redirectToRoute('app_reservation_cart');
        }
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        // Security: ensure users can only see their own reservations
        if ($reservation->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You cannot view this reservation.');
        }

        $isAdminView = $this->isGranted('ROLE_ADMIN') && $reservation->getUser() !== $this->getUser();
        $totalCost = $this->calculateReservationCost($reservation);

        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
            'is_admin_view' => $isAdminView,
            'total_cost' => $totalCost
        ]);
    }
}