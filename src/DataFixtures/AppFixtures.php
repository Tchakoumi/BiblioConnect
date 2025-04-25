<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Category;
use App\Entity\Author;
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

        $categories = [
            'Science Fiction',
            'Fantasy',
            'Mystery',
            'Romance',
            'Horror',
            'Thriller',
            'Biography',
        ];

        foreach ($categories as $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);
        }

        $authors = [
            'J.K. Rowling',
            'Stephen King',
            'Agatha Christie',
            'Dan Brown',
            'J.R.R. Tolkien',
        ];

        foreach ($authors as $authorName) {
            $author = new Author();
            $author->setFullName($authorName);
            $manager->persist($author);
        }

        $manager->flush();
    }
}