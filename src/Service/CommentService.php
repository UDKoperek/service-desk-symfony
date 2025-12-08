<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CommentService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addComment(Comment $comment, Ticket $ticket, User $author): void
    {
        $comment->setAuthor($author);
        $comment->setTicket($ticket);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();
    }
}