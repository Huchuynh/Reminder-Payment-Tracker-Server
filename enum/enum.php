<?php

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case EXPIRING = 'expiring';
    case CANCELLED = 'cancelled';
    case OVERDUE = 'overdue';
}

enum AlertChanels: string
{
    case EMAIL = 'email';
    case IN_APP = 'in_app';
    case PUSH = 'push';
}
