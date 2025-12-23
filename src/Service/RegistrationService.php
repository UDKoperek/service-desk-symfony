<?php

namespace App\Service;

use App\Entity\User;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class RegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly EmailVerifier $emailVerifier,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function registerUser(User $user, string $plainPassword): void
    {
        
        $errors = $this->validator->validate($user, null, ['Default', 'registration']);

        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string) $errors);
        }

        $hashedPassword = $this->userPasswordHasher->hashPassword(
            $user,
            $plainPassword
        );

        $user->setPassword($hashedPassword);
        $user->setIsVerified(false);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}