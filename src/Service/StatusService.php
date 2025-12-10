<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Ticket;
use App\Entity\User;

class StatusService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {
    }

  
    public function changeStatus(Ticket $ticket): void
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            if ($ticket->getStatus()->value === 'Closed') {
                throw new \Exception('Tylko administrator może zamknąć zgłoszenie.');
            }
        }
        
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();
    }
}