<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Ticket;
use App\Enum\TicketStatus;
use App\Enum\TicketPriority;
use App\Dto\TicketFilterDto;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    /**
     * Znajduje bilety należące do zalogowanego użytkownika LUB do danej sesji.
     * * @param int|null $authorId ID zalogowanego użytkownika (jeśli jest)
     * @param string|null $sessionToken Token sesji anonimowego użytkownika (jeśli jest)
     * @return Ticket[]
     */
    public function findTicketsForUser(?int $authorId, ?string $sessionToken): array
    {
        $qb = $this->createQueryBuilder('t');

        if ($authorId !== null) {
            $qb->where('t.author = :authorId')
               ->setParameter('authorId', $authorId);

        } elseif ($sessionToken !== null) {
            $qb->where('t.sessionToken = :sessionToken')
               ->setParameter('sessionToken', $sessionToken);
        } else {
            return []; 
        }

        $qb->orderBy('t.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function getFilteredTicketsQuery(
        TicketFilterDto $filters,
        ?User $user = null,
        ?string $anonymousToken = null, 
        bool $isAgentOrAdmin = false
    ): Query
    {
        $qb = $this->createQueryBuilder('t')
                   ->addSelect('a')
                   ->leftJoin('t.category', 'c')
                   ->leftJoin('t.author', 'a');

        if (!$isAgentOrAdmin) {

            if ($user !== null) { 
                $qb->andWhere('t.author = :user')
                   ->setParameter('user', $user);
                   
            } elseif ($anonymousToken !== null) { 
                $qb->andWhere('t.sessionToken = :token')
                   ->setParameter('token', $anonymousToken);
                   
            } else {
                $qb->andWhere('1 = 0'); 
            }
        }

        if ($filters->status) {
            $statusEnum = TicketStatus::tryFrom($filters->status);
            if ($statusEnum) {
                $qb->andWhere('t.status = :status')
                   ->setParameter('status', $statusEnum->value); 
            }
        }
        
        if ($filters->priority) {
            
            $priorityKey = strtoupper($filters->priority);
            $priorityEnum = null;

            try {
                $priorityEnum = \App\Enum\TicketPriority::from($priorityKey);
            } catch (\ValueError $e) {}

            if ($priorityEnum) {
                $qb->andWhere('t.priority = :priority') 
                ->setParameter('priority', $priorityEnum->value); 
            }
        }

        if ($filters->search) {
            $qb->andWhere('t.title LIKE :search OR t.content LIKE :search')
               ->setParameter('search', '%' . $filters->search . '%');
        }

        $qb->orderBy('t.' . $filters->sortBy, $filters->sortOrder);

        return $qb->getQuery();
    }
}
