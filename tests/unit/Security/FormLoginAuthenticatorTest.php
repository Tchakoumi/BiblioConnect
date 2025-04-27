<?php

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\FormLoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

/**
 * Simple login functionality tests
 */
class FormLoginAuthenticatorTest extends KernelTestCase
{
    // Constants for test user
    private const TEST_EMAIL = 'test-login@example.com';
    private const TEST_PASSWORD = 'Password123!';

    // Services needed for testing
    private FormLoginAuthenticator $authenticator;
    private UrlGeneratorInterface $router;
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Set up before each test
     */
    protected function setUp(): void
    {
        // Boot the Symfony kernel to access services
        self::bootKernel();

        // Get services from container
        $container = self::getContainer();
        $this->router = $container->get(UrlGeneratorInterface::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);

        // Create the authenticator with the router service
        $this->authenticator = new FormLoginAuthenticator($this->router);
    }

    /**
     * Test 1: Can a user authenticate with correct credentials?
     */
    public function testUserCanAuthenticateWithCorrectCredentials(): void
    {
        // Create a login request with correct credentials
        $request = $this->createLoginRequest(self::TEST_EMAIL, self::TEST_PASSWORD);

        // Try to authenticate
        $passport = $this->authenticator->authenticate($request);

        // Check if we got a user badge
        $userBadge = $passport->getBadge(UserBadge::class);

        // Assert that the user email matches what we provided
        $this->assertEquals(self::TEST_EMAIL, $userBadge->getUserIdentifier());
    }

    /**
     * Test 2: After login, user is redirected to homepage
     */
    public function testUserIsRedirectedAfterLogin(): void
    {
        // Create a request
        $request = $this->createLoginRequest(self::TEST_EMAIL, self::TEST_PASSWORD);

        // Mock a token since we can't create a real one in tests
        $token = $this->createMock(TokenInterface::class);

        // Get the response after successful authentication
        $response = $this->authenticator->onAuthenticationSuccess($request, $token, 'main');

        // Check if it's a redirect
        $this->assertInstanceOf(RedirectResponse::class, $response);

        // Check if URL contains the homepage indicator
        $url = $response->getTargetUrl();
        $this->assertStringNotContainsString('login', $url);
    }

    /**
     * Test 3: Test password validation works
     */
    public function testPasswordValidationWorks(): void
    {
        // Create a test user
        $user = new User();

        // Set up a password and hash it
        $plainPassword = self::TEST_PASSWORD;
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // Check if correct password validates
        $validPassword = $this->passwordHasher->isPasswordValid($user, $plainPassword);
        $this->assertTrue($validPassword, 'Correct password should validate');

        // Check if wrong password fails
        $invalidPassword = $this->passwordHasher->isPasswordValid($user, 'WrongPassword');
        $this->assertFalse($invalidPassword, 'Wrong password should not validate');
    }

    /**
     * Helper function to create a login request
     */
    private function createLoginRequest(string $email, string $password): Request
    {
        // Create a new request with the parameters directly
        $request = Request::create(
            '/login',
            'POST',
            [
                '_username' => $email,
                '_password' => $password,
                '_csrf_token' => 'dummy-token'
            ]
        );

        // Create and set a session
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        return $request;
    }
}