<?php

namespace App\Enum;

enum TicketPriority: string
{
    case ABSENCE = 'Brak';
    case LOW = 'In Progress';
    case MEDIUM = 'Waiting for User';
    case HIGH = 'Closed';
}