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

    public function createAndAddComment(Comment $comment, Ticket $ticket, ?User $user, ?string $anonymousToken): void {
        
        $comment->setTicket($ticket);
  
        if ($user) {
            $comment->setAuthor($user);
            $comment->setAnonymousToken(null);
        } elseif ($anonymousToken) {
            $comment->setAnonymousToken($anonymousToken); 
            $comment->setAuthor(null);
        } else {
        }

        $this->entityManager->persist($comment);
        $this->entityManager->flush();
    }
}