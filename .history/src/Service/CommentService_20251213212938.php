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

    public function createAndAddComment(string $content, Ticket $ticket, ?User $author, ?string $anonymousToken = null): void
    {
        $comment = new Comment();
        $comment->setContent($content);
        $comment->setTicket($ticket);
        
        if ($author !== null) {
            $comment->setAuthor($author);
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