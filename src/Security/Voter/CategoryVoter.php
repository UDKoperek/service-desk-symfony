<?php

namespace App\Security\Voter;

use App\Entity\Category;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;

final class CategoryVoter extends Voter
{
    public const SHOW = 'SHOW_CATEGORY';
    public const CREATE = 'CREATE_CATEGORY';
    public const EDIT = 'EDIT_CATEGORY';
    public const DELETE = 'DELETE_CATEGORY';

    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::SHOW, self::CREATE, self::EDIT, self::DELETE])
            && ($subject instanceof Category || ($subject === null && $attribute === self::CREATE));
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser(); 
        /** @var Category|null $category */
        $category = $subject; 

        // Wszystkie akcje wymagają zalogowanego użytkownika (User) i roli ROLE_ADMIN
        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::SHOW => $this->canShow($user), 
            self::CREATE => $this->canCreate($user), 
            self::EDIT => $this->canEdit($category, $user), 
            self::DELETE => $this->canDelete($category, $user), 
            default => false,
        };
    }

    private function canShow(User $user): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canCreate(User $user): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canEdit(?Category $category, User $user): bool
    {
        if (!$category instanceof Category) {
             return false;
        }
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canDelete(?Category $category, User $user): bool
    {
        if (!$category instanceof Category) {
             return false;
        }
        return $this->security->isGranted('ROLE_ADMIN');
    }
}