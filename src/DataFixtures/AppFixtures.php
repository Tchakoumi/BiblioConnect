<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            [
                'email' => 'user@biblioconnect.com',
                'firstName' => 'User',
                'lastName' => 'Account',
                'role' => 'ROLE_USER'
            ],
            [
                'email' => 'admin@biblioconnect.com',
                'firstName' => 'Admin',
                'lastName' => 'Account',
                'role' => 'ROLE_ADMIN'
            ],
            [
                'email' => 'librarian@biblioconnect.com',
                'firstName' => 'Librarian',
                'lastName' => 'Account',
                'role' => 'ROLE_LIBRARIAN'
            ]
        ];

        foreach ($users as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setFirstName($userData['firstName']);
            $user->setLastName($userData['lastName']);
            $user->setRoles([$userData['role']]);

            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                'Testing123!'
            );
            $user->setPassword($hashedPassword);

            $manager->persist($user);
        }

        $manager->flush();
    }
}