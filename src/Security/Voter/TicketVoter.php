<?php

namespace App\Security\Voter;

use App\Entity\Ticket;
use App\Entity\User;
use App\Service\TicketService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;

final class TicketVoter extends Voter
{
    public const SHOW = 'GET_SHOW';
    public const EDIT = 'POST_EDIT';
    public const DELETE = 'POST_DELETE';

    public function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack, // 🔑 Konieczna zależność!
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::SHOW, self::EDIT, self::DELETE])
            && $subject instanceof \App\Entity\Ticket;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser(); // User entity, 'anon.' string, lub inny anonimowy obiekt

        /** @var Ticket $ticket */
        $ticket = $subject; 

        // 1. Sprawdzamy, czy token reprezentuje faktycznego, zalogowanego użytkownika (User Entity)
        if ($user instanceof User) {
            // Logika dla ZALOGOWANYCH: Przekazujemy User entity
            return match ($attribute) {
                self::SHOW => $this->canShowAuthenticated($ticket, $user), 
                self::EDIT => $this->canEditAuthenticated($ticket, $user),
                self::DELETE => $this->canDeleteAuthenticated($ticket, $user),
                default => false,
            };
        } 
        
        // 2. Jeśli NIE jest to User Entity, traktujemy jako ANONIMOWY dostęp
        else {
            // Logika dla ANONIMOWYCH: Nie przekazujemy obiektu User, używamy tylko tokena sesji
            return match ($attribute) {
                self::SHOW => $this->canShowAnonymous($ticket), 
                self::EDIT => $this->canEditAnonymous($ticket),
                // Anonimowy użytkownik nigdy nie może usuwać
                self::DELETE => false,
                default => false,
            };
        }
    }

    // --- LOGIKA DLA ZALOGOWANYCH UŻYTKOWNIKÓW (CZYSTY TYP User) ---
    
    private function canShowAuthenticated(Ticket $ticket, User $user): bool
    {
        // ... (Logika: ADMIN/AGENT lub właściciel) ...
        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_AGENT')) {
            return true;
        }
        $author = $ticket->getAuthor();
        return ($author !== null && $user->getId() === $author->getId());
    }

    private function canEditAuthenticated(Ticket $ticket, User $user): bool
    {
        // ... (Logika: ADMIN/AGENT lub właściciel) ...
        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_AGENT')) {
            return true;
        }
        $author = $ticket->getAuthor();
        return ($author !== null && $user->getId() === $author->getId());
    }

    private function canDeleteAuthenticated(Ticket $ticket, User $user): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
    
    // --- LOGIKA DLA ANONIMOWYCH UŻYTKOWNIKÓW (BRAK User) ---

    private function canShowAnonymous(Ticket $ticket): bool
    {
        // Sprawdzenie uprawnień przez token sesji
        $currentSessionId = $this->requestStack->getSession()->getId();
        return ($ticket->getSessionToken() === $currentSessionId);
    }

    private function canEditAnonymous(Ticket $ticket): bool
    {
        // Sprawdzenie uprawnień przez token sesji
        $currentSessionId = $this->requestStack->getSession()->getId();
        return ($ticket->getSessionToken() === $currentSessionId);
    }
}