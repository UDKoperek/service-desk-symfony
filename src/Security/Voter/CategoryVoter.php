<?php

namespace App\Security\Voter;

use App\Entity\Category;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Voter do zarządzania dostępem do akcji na Category (CRUD).
 * Oparty na statycznej roli ROLE_ADMIN.
 */
final class CategoryVoter extends Voter
{
    // Używamy uogólnionych nazw, ponieważ nie są to Votery dynamiczne, ale spójne z TicketVoter
    public const SHOW = 'SHOW_CATEGORY';
    public const EDIT = 'EDIT_CATEGORY';
    public const DELETE = 'DELETE_CATEGORY';
    public const CREATE = 'CREATE_CATEGORY'; // Akcja 'new'

    public function __construct(
        private readonly Security $security,
    ) {
    }

    /**
     * Sprawdza, czy Voter obsługuje dany atrybut i obiekt.
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Sprawdzamy, czy atrybut jest jednym z obsługiwanych, a obiekt jest Category (lub null dla CREATE)
        return in_array($attribute, [self::SHOW, self::EDIT, self::DELETE, self::CREATE])
            // Umożliwiamy subject = null tylko dla akcji CREATE (bo tworzymy nową, której jeszcze nie ma)
            && ($subject instanceof Category || ($subject === null && $attribute === self::CREATE));
    }

    /**
     * Decyduje, czy użytkownik ma dostęp.
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        dd($this->security->isGranted('ROLE_ADMIN'));
        // 1. SHOW - Dostępne publicznie (dla każdego)
        if ($attribute === self::SHOW_CATEGORY) {
            return true; 
        }

        // 2. CREATE, EDIT, DELETE - Tylko dla zalogowanych ADMINÓW
        // Anonimowi użytkownicy (anon. string) i niezalogowani automatycznie odpadają
        if (!$user instanceof User) {
            return false;
        }

        // Sprawdzenie główne: Czy użytkownik jest ADMINEM?
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }
        
        // Jeśli nie jest adminem, zabraniamy edycji/usuwania/tworzenia
        return false;
    }
}