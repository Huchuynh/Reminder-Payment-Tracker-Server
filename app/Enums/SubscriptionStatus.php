<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case EXPIRING = 'expiring';
    case CANCELED = 'canceled';
    case PAID = 'paid';

    public function isRemindable(): bool
    {
        return match ($this) {
            self::ACTIVE, self::EXPIRED, self::EXPIRING, self::CANCELED, self::PAID => true,
            default => false
        };
    }

    public static function remindableValues(): array
    {
        return array_map(
            fn(self $case) => $case->value,
            array_filter(self::cases(), fn(self $case) => $case->isRemindable())
        );
    }
}
