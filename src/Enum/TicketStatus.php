<?php

namespace App\Enum;

enum TicketStatus:
{
    case NEW = 'New';
    case IN_PROGRESS = 'In Progress';
    case WAITING_FOR_USER = 'Waiting for User';
    case CLOSED = 'Closed';
}