<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Ticket;
use App\Service\AnonymousTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\UserRepository;
use App\Repository\TicketRepository;

final class TicketService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly AnonymousTokenService $anonymousTokenService,
        private readonly TicketRepository $ticketRepository,
        private readonly UserRepository $userRepository,
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
            $anonymousUser = $this->userRepository->findOneBy(['username' => 'anonymous_submitter']);
            if (!$anonymousUser) {
                throw new \Exception('Brak konta anonimowego nadawcy.');
            }
            $ticket->setAuthor($anonymousUser);

            // 2b. Przypisz unikalne ID sesji jako token zabezpieczający
            $longTermToken = $this->anonymousTokenService->getOrCreateToken(); 
            $ticket->setSessionToken($longTermToken);
        }
        
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();
    }

    public function getTicketsForCurrentUser(): array
    {
        $user = $this->security->getUser();
        $repository = $this->entityManager->getRepository(Ticket::class);

        // 1. ADMIN LUB AGENT: ZOBACZ WSZYSTKO
        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_AGENT')) {
            return $repository->findAll(); 
        }

        // 2. ZALOGOWANY UŻYTKOWNIK (Klient)
        if ($user instanceof User) {
            // Zwróć bilety, gdzie użytkownik jest autorem
            return $repository->findBy(['author' => $user]);
        }
        
        // 3. ANONIMOWY UŻYTKOWNIK
        
        // Pobierz token z ciasteczka
        $anonymousToken = $this->anonymousTokenService->getOrCreateToken(); // Zakładam, że ten serwis jest tu dostępny

        if ($anonymousToken) {
            // Zwróć bilety, gdzie token sesyjny pasuje
            return $repository->findBy(['sessionToken' => $anonymousToken]);
        }

        // 4. Całkowicie nieautoryzowany lub brak tokena
        return [];
    }

    public function saveChanges(): void
    {
        $this->entityManager->flush();
    }

    public function deleteTicket(Ticket $ticket): void
    {
        $this->entityManager->remove($ticket);
              
        $this->entityManager->flush();
    }
}