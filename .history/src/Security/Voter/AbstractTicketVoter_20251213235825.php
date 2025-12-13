<?php

namespace App\Security\Voter;

use App\Entity\Ticket;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;

// Klasa bazowa dla Voterów
abstract class AbstractTicketVoter extends Voter
{
    // Użycie stałych z unikalnymi nazwami, aby uniknąć konfliktu z PHP keyword (DELETE)
    public const SHOW = 'SHOW_TICKET';
    public const EDIT = 'EDIT_TICKET';
    public const DELETE = 'DELETE_TICKET';

    public function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * Sprawdza, czy Voter obsługuje dany atrybut i obiekt (subject).
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::SHOW, self::EDIT, self::DELETE])
            && $subject instanceof Ticket;
    }

    /**
     * Decyduje, czy użytkownik ma dostęp do atrybutu (akcji) na danym obiekcie.
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // $user może być User entity, 'anon.' string, lub inny anonimowy obiekt

        $user = $token->getUser();

        /** @var Ticket $ticket */
        $ticket = $subject;

        // 1. Sprawdzamy, czy token reprezentuje faktycznego, zalogowanego użytkownika (User Entity)
        if ($user instanceof User) {
            // Logika dla ZALOGOWANYCH
            return match ($attribute) {
                self::SHOW => $this->canShowAuthenticated($ticket, $user),
                self::EDIT => $this->canEditAuthenticated($ticket, $user),
                self::DELETE => $this->canDeleteAuthenticated($ticket, $user),
                default => false,
            };
        }

        // 2. Jeśli NIE jest to User Entity, traktujemy jako ANONIMOWY dostęp
        // (np. $user jest typu string 'anon.' lub anonimowym obiektem)
        else {
            // Logika dla ANONIMOWYCH
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
        // ADMIN/AGENT lub właściciel może zobaczyć
        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_AGENT')) {
            return true;
        }
        $author = $ticket->getAuthor();
        // Sprawdza, czy ticket ma autora i czy ID autora zgadza się z ID zalogowanego użytkownika
        return ($author !== null && $user->getId() === $author->getId());
    }

    private function canEditAuthenticated(Ticket $ticket, User $user): bool
    {
        // ADMIN/AGENT lub właściciel może edytować
        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_AGENT')) {
            return true;
        }
        $author = $ticket->getAuthor();
        // Sprawdza, czy ticket ma autora i czy ID autora zgadza się z ID zalogowanego użytkownika
        return ($author !== null && $user->getId() === $author->getId());
    }

    private function canDeleteAuthenticated(Ticket $ticket, User $user): bool
    {
        // Tylko ADMIN może usuwać
        return $this->security->isGranted('ROLE_ADMIN');
    }

    // --- LOGIKA DLA ANONIMOWYCH UŻYTKOWNIKÓW (BRAK User) ---

    private function canShowAnonymous(Ticket $ticket): bool
    {
        // Sprawdzenie uprawnień przez token sesji (musi się zgadzać z tokenem w Tickecie)
        $currentSessionId = $this->requestStack->getSession()->getId();
        return ($ticket->getSessionToken() === $currentSessionId);
    }

    private function canEditAnonymous(Ticket $ticket): bool
    {
        // Sprawdzenie uprawnień przez token sesji (musi się zgadzać z tokenem w Tickecie)
        $currentSessionId = $this->requestStack->getSession()->getId();
        return ($ticket->getSessionToken() === $currentSessionId);
    }
}
