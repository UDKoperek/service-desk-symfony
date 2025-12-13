<?php

namespace App\Service;

use App\Entity\User;
use App\Security\EmailVerifier; // Importujemy EmailVerifier
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class RegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly EmailVerifier $emailVerifier,
    ) {
    }

    public function registerUser(User $user, string $plainPassword): void
    {
        // 1. Haszowanie i Zapis Użytkownika
        $hashedPassword = $this->userPasswordHasher->hashPassword(
            $user,
            $plainPassword
        );
        $user->setPassword($hashedPassword);
        $user->setIsVerified(false); // Ustawiamy na niewerified przed zapisem

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}