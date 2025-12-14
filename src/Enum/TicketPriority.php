<?php

namespace App\Enum;

enum TicketPriority: string
{
    case ABSENCE = 'Brak';
    case LOW = 'Niski';
    case MEDIUM = 'Średni';
    case HIGH = 'Wysoki';
}