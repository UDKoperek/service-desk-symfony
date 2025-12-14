<?php

namespace App\EventSubscriber;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security; 

class TicketUpdateSubscriber implements EventSubscriberInterface
{
    public function __construct(private Security $security)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate,
        ];
    }


    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Ticket) {
            return;
        }

        $isAgentOrAdmin = $this->security->isGranted('ROLE_AGENT') || $this->security->isGranted('ROLE_ADMIN');

        if (!$isAgentOrAdmin) {
            if ($args->hasChangedField('status')){

                $oldStatus = $args->getOldValue('status');
                $args->setNewValue('status', $oldStatus->value);
            }
            if ($args->hasChangedField('priority')) {

                $oldPriority = $args->getOldValue('priority');
                $args->setNewValue('priority', $oldPriority->value);
            }
        }
    }
}