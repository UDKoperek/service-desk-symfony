<?php

namespace App\Enum;

enum TicketPriority: string
{
    case ABSENCE = 'ABSENCE';
    case LOW = 'LOW';
    case MEDIUM = 'MEDIUM';
    case HIGH = 'HIGH';
}