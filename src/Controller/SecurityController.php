<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        LoggerInterface $logger,
        RequestStack $requestStack
    ): Response
    {
        // If the user is already logged in, redirect to homepage
        if ($this->getUser()) {
            return $this->redirectToRoute('app_homepage');
        }

        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // Log the error if any
        if ($error) {
            $logger->error('Login error: ' . $error->getMessage());

            // Add flash message for additional debugging
            $this->addFlash('error', 'Authentication error: ' . $error->getMessage());
        }

        // Log the request method and parameters for debugging
        $request = $requestStack->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $logger->info('Login attempt', [
                'method' => $request->getMethod(),
                'username_param' => $request->request->get('_username'),
                'has_password' => $request->request->has('_password'),
                'csrf_token_present' => $request->request->has('_csrf_token'),
            ]);
        }

        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/debug-users', name: 'app_debug_users')]
    public function debugUsers(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        // Only available in dev environment
        if ($this->getParameter('kernel.environment') !== 'dev') {
            throw $this->createNotFoundException('Debug route not available');
        }

        $users = $entityManager->getRepository(User::class)->findAll();
        $debugInfo = [];

        foreach ($users as $user) {
            // Test a known password against this user for debugging
            $testPassword = 'Testing123!';
            $isPasswordValid = $passwordHasher->isPasswordValid($user, $testPassword);

            $debugInfo[] = [
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'known_password_works' => $isPasswordValid ? 'Yes' : 'No'
            ];
        }

        return $this->render('security/debug.html.twig', [
            'debug_info' => $debugInfo
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET', 'POST'])]
    public function logout(): never
    {
        // This method can be empty - it will be intercepted by the logout key in security.yaml
        // This is never reached, but required for the route to exist
        throw new \LogicException('This method should not be reached - check security.yaml logout configuration!');
    }
}