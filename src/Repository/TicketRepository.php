<?php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
            // ZALOGOWANY UŻYTKOWNIK: filtrowanie po polu 'author' (które jest relacją do encji User)
            $qb->where('t.author = :authorId')
               ->setParameter('authorId', $authorId);

        } elseif ($sessionToken !== null) {
            // ANONIMOWY UŻYTKOWNIK: filtrowanie po polu 'sessionToken'
            $qb->where('t.sessionToken = :sessionToken')
               ->setParameter('sessionToken', $sessionToken);
        } else {
            // Brak kryteriów (np. niezalogowany, ale bez aktywnej sesji lub tokena)
            // Zwracamy pustą listę lub wszystkie (jeśli to publiczna strona)
            // Zasadniczo: zwracamy pustą listę dla bezpieczeństwa
            return []; 
        }

        $qb->orderBy('t.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
