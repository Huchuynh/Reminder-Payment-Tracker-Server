<?php

namespace App\Enums;

enum SubscriptionHistoryAction: string
{
    case RENEWED = 'renewed';
    case EXPIRED = 'expired';
    case CANCELED = 'canceled';
}
