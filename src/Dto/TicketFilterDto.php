<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpKernel\Attribute\MapQueryString; 

final class TicketFilterDto
{
    public ?string $search = null;
 
    #[Assert\Choice(
        choices: ['NEW', 'IN_PROGRESS', 'WAITING_FOR_USER', 'CLOSED', null, ''],
        message: 'Niepoprawna wartość statusu.'
    )]
    public ?string $status = null;

    #[Assert\Choice(
        choices: ['ABSENCE', 'LOW', 'MEDIUM', 'HIGH', null, ''],
        message: 'Niepoprawna wartość priorytetu.'
    )]
    public ?string $priority = null;


    #[Assert\Choice(choices: ['createdAt', 'priority', 'status', 'title', 'id'])]
    public string $sortBy = 'createdAt';


    #[Assert\Choice(choices: ['asc', 'desc'])]
    public string $sortOrder = 'desc';


    #[Assert\Positive]
    public int $page = 1;
}