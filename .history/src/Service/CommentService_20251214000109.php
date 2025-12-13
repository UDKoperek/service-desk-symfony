<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Ticket;
use App\Entity\User;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;

class CommentService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addComment(string $content, Ticket $ticket, ?User $user, ?string $anonymousToken = null): void
    {
        $comment = new Comment();
        $comment->setContent($content);
        $comment->setTicket($ticket);

        if ($user !== null) {
            $comment->setAuthor($user);
        } elseif ($anonymousToken !== null) {
            $comment->setAnonymousToken($anonymousToken);
        } else {
            // To jest przypadek błędu (komentarz bez autora/tokenu)
            throw new \LogicException('Komentarz musi mieć autora (User) lub token anonimowy.');
        }

        $this->entityManager->persist($comment);
        $this->entityManager->flush();
    }
}
