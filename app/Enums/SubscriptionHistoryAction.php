<?php

namespace App\Enums;

enum SubscriptionHistoryAction: string
{
    case RENEWED = 'renewed';
    case EXPIRED = 'expired';
    case CANCELED = 'canceled';

    public function isRemindable(): bool
    {
        return match ($this) {
            self::RENEWED, self::EXPIRED, self::CANCELED => true,
            default => false
        };
    }

    public static function remindableValues(): array
    {
        return array_map(
            fn (self $case) => $case->value,
            array_filter(self::cases(), fn (self $case) => $case->isRemindable())
        );
    }
}
