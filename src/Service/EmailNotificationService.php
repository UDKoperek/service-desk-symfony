<?php

namespace App\Service;

use App\Entity\User;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class EmailNotificationService
{
    public function __construct(
        private readonly EmailVerifier $emailVerifier
    ) {
    }

    
    public function sendRegistrationConfirmationEmail(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('denysukrainskyi@gmail.com', 'Service Desk')) 
            ->to($user->getEmail())
            ->subject('Potwierdzenie rejestracji w Service Desk')
            ->htmlTemplate('registration/confirmation_email.html.twig');

        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user, $email);
    }
}