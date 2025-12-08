<?php

namespace App\Security\Voter;

use App\Entity\Ticket;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

final class TicketVoter extends Voter
{
    public const SHOW = 'GET_SHOW';
    public const EDIT = 'POST_EDIT';
    public const DELETE = 'POST_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::SHOW, self::EDIT, self::DELETE])
            && $subject instanceof \App\Entity\Ticket;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        /** @var Ticket $ticket */
        $ticket = $subject; 

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::SHOW:
                return $this->canShow($ticket, $user); 
                
            case self::EDIT:
                return $this->canEdit($ticket, $user);

            case self::DELETE:
                return $this->canDelete($ticket, $user);
        }

        return false;
    }

    private function canShow(Ticket $ticket, User $user): bool
    {
        // 1. ADMIN always granted
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }
        
        // 2. AGENT can show all tickets
        if ($this->security->isGranted('ROLE_AGENT')) {
            return true;
        }

        // 3.Ticket's creator (ROLE_USER) can show only his ticket
        // Check is user author of the ticket
        if ($user === $ticket->getAuthor()) {
            return true;
        }

        return false;
    }

    private function canEdit(Ticket $ticket, User $user): bool
    {
        // 1. ADMIN always granted
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }
        
        // 2. AGENT can edit all tickets
        if ($this->security->isGranted('ROLE_AGENT')) {
            return true;
        }

        // 3.Ticket's creator (ROLE_USER) can edit only his ticket
        // Check is user author of the ticket
        if ($user === $ticket->getAuthor()) {
            return true;
        }

        return false;
    }

    private function canDelete(Ticket $ticket, User $user): bool
    {
        // Tylko ADMINISTRATOR ma uprawnienia do usuwania zgłoszeń
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
