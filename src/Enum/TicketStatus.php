<?php

namespace App\Enum;

enum TicketStatus: string
{
    case NEW = 'NEW';
    case IN_PROGRESS = 'IN_PROGRESS';
    case WAITING_FOR_USER = 'WAITING_FOR_USER';
    case CLOSED = 'CLOSED';
}