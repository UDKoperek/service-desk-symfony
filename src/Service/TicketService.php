<?php

namespace App\Service;

use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

final class TicketService
{
    private const ANONYMOUS_USER_ID = 100;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly AnonymousTokenService $tokenService,
    ) {
    }

    public function processNewTicket(Ticket $ticket): void
    {
        $user = $this->security->getUser();
        
        if ($user instanceof User) {
            // 1. Zalogowany użytkownik: ustaw go jako autora
            $ticket->setAuthor($user);
            $ticket->setSessionToken(null); 
        } else {
            // 2. Anonimowy użytkownik:
            
            // 2a. Ustaw stałego użytkownika 'anonymous_submitter'
            $anonymousUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'anonymous_submitter']);
            if (!$anonymousUser) {
                throw new \Exception('Brak konta anonimowego nadawcy.');
            }
            $ticket->setAuthor($anonymousUser);

            // 2b. Przypisz unikalne ID sesji jako token zabezpieczający
            $sessionId = $this->requestStack->getSession()->getId();
            $ticket->setSessionToken($sessionId); 
        }
        
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();
    }
}