<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Ticket;
use App\Enum\TicketStatus;
use App\Enum\TicketPriority;
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
            $ticket->setAuthor($user);
            $ticket->setSessionToken(null);

            $isAgentOrAdmin = $this->security->isGranted('ROLE_AGENT') || $this->security->isGranted('ROLE_ADMIN');
            if (!$isAgentOrAdmin)
            {
                $ticket->setPriority(TicketPriority::ABSENCE);
                $ticket->setStatus(TicketStatus::NEW);
            }
        } else {
            
            $anonymousUser = $this->userRepository->findOneBy(['username' => 'anonymous_submitter']);
            if (!$anonymousUser) {
            }

            $ticket->setAuthor($anonymousUser);
            $ticket->setPriority(TicketPriority::ABSENCE);
            $ticket->setStatus(TicketStatus::NEW);

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

        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_AGENT')) {
            return $repository->findAll(); 
        }

        if ($user instanceof User) {

            return $repository->findBy(['author' => $user]);
        }
        
        $anonymousToken = $this->anonymousTokenService->getOrCreateToken(); // Zakładam, że ten serwis jest tu dostępny

        if ($anonymousToken) {
            return $repository->findBy(['sessionToken' => $anonymousToken]);
        }

        return [];
    }

    public function editTicket(Ticket $ticket): void
    {
        $user = $this->security->getUser();
        
        if ($user instanceof User) {
            $isAgentOrAdmin = $this->security->isGranted('ROLE_AGENT') || $this->security->isGranted('ROLE_ADMIN');
            if ($isAgentOrAdmin)
            {
                $ticket->setPriority(TicketPriority::ABSENCE);
                $ticket->setStatus(TicketStatus::NEW);
            }
        } else {
            
            $anonymousUser = $this->userRepository->findOneBy(['username' => 'anonymous_submitter']);
            if (!$anonymousUser) {
            }

            $ticket->setAuthor($anonymousUser);
            $ticket->setPriority(TicketPriority::ABSENCE);
            $ticket->setStatus(TicketStatus::NEW);

            $longTermToken = $this->anonymousTokenService->getOrCreateToken(); 
            $ticket->setSessionToken($longTermToken);
        }
        
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();
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