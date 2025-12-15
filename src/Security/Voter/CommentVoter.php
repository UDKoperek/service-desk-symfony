<?php

namespace App\Security\Voter;

use App\Entity\Ticket;
use App\Entity\User;
use App\Service\AnonymousTokenService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;

class CommentVoter extends Voter
{
    public const COMMENT_ON_TICKET = 'COMMENT_ON_TICKET';

    public function __construct(
        private readonly Security $security,
        private readonly AnonymousTokenService $anonymousTokenService)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::COMMENT_ON_TICKET
            && $subject instanceof Ticket;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        $ticket = $subject;
        
        if ($user instanceof User) {
            return $this->canCommentAuthenticated($ticket, $user);
        } 
        else {
            return $this->canCommentAnonymous($ticket);
        }
    }

    private function canCommentAuthenticated(Ticket $ticket, User $user): bool
    {

        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_AGENT')) {
            return true;
        }

        $ticketStatus = $ticket->getStatus();
        
        if ($ticketStatus->value === "Closed")
        {
            return false;
        }

        $author = $ticket->getAuthor();

        if ($author !== null && $author->getId() === $user->getId()) {
            return true;
        }
        return false;
    }

    private function canCommentAnonymous(Ticket $ticket): bool
    {
        $ticketStatus = $ticket->getStatus();

        if ($ticketStatus->value === "Closed")
        {
            return false;
        }

        try {
            $currentAnonymousToken = $this->anonymousTokenService->getOrCreateToken(); 
        } catch (\Exception) {
            return false;
        }
        
        if (!$currentAnonymousToken) {
            return false;
        }
        
        return ($ticket->getSessionToken() === $currentAnonymousToken);
    }
}