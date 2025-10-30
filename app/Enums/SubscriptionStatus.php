<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case EXPIRING = 'expiring';
    case CANCELLED = 'cancelled';
    case OVERDUE = 'overdue';
}
