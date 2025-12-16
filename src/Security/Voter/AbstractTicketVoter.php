<?php

namespace App\Security\Voter;

use App\Entity\Ticket;
use App\Entity\User;
use App\Service\AnonymousTokenService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;

// Klasa bazowa dla Voterów
abstract class AbstractTicketVoter extends Voter 
{
    public const SHOW = 'SHOW_TICKET';
    public const EDIT = 'EDIT_TICKET';
    public const DELETE = 'DELETE_TICKET'; 

    public function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly AnonymousTokenService $anonymousTokenService,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::SHOW, self::EDIT, self::DELETE])
            && $subject instanceof Ticket;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser(); 
        /** @var Ticket $ticket */
        $ticket = $subject; 

        if ($user instanceof User) {
            return match ($attribute) {
                self::SHOW => $this->canShowAuthenticated($ticket, $user), 
                self::EDIT => $this->canEditAuthenticated($ticket, $user),
                self::DELETE => $this->canDeleteAuthenticated($ticket, $user),
                default => false,
            };
        } 
        else {
            return match ($attribute) {
                self::SHOW => $this->canShowAnonymous($ticket), 
                self::EDIT => $this->canEditAnonymous($ticket),
                self::DELETE => false,
                default => false,
            };
        }
    }

    private function canShowAuthenticated(Ticket $ticket, User $user): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_AGENT')) {
            return true;
        }
        $author = $ticket->getAuthor();
        return ($author !== null && $user->getId() === $author->getId());
    }

    private function canEditAuthenticated(Ticket $ticket, User $user): bool
    {
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
    
    // --- LOGIKA DLA ANONIMOWYCH UŻYTKOWNIKÓW ---

    private function canShowAnonymous(Ticket $ticket): bool
    {
        try {

            $currentAnonymousToken = $this->anonymousTokenService->getOrCreateToken();
        } catch (\Exception) {
            return false;
        }
        
        return ($ticket->getSessionToken() === $currentAnonymousToken);
    }

    private function canEditAnonymous(Ticket $ticket): bool
    {
        try {
            
            $currentAnonymousToken = $this->anonymousTokenService->getOrCreateToken();
        } catch (\Exception) {
            return false;
        }
        
        return ($ticket->getSessionToken() === $currentAnonymousToken);
    }
}