<?php

namespace App\Tests\Unit\Security;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * A very simple test for password validation in the login process
 */
class LoginPasswordTest extends TestCase
{
    /**
     * Test basic user password operations
     */
    public function testUserPasswordOperations(): void
    {
        // Create a test user
        $user = new User();

        // TEST 1: Initial password should be null
        $this->assertNull($user->getPassword(), 'Initial password should be null');

        // TEST 2: Setting and getting password works
        $testPassword = 'hashedPassword123';
        $user->setPassword($testPassword);
        $this->assertEquals($testPassword, $user->getPassword(), 'Password should be retrievable');

        // TEST 3: Check the eraseCredentials method exists
        $this->assertTrue(method_exists($user, 'eraseCredentials'), 'User should have eraseCredentials method');

        // We don't test the actual hashing here - that's more of an integration test
        // requiring the password hasher service
    }
}