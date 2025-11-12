<?php

namespace App\Enums;

enum StatisticPeriodFilter: string
{
    case THIS_MONTH = 'this_month';
    case LAST_MONTH = 'last_month';

    public function isValidated(): bool
    {
        return match ($this) {
            self::THIS_MONTH, self::LAST_MONTH => true,
            default => false
        };
    }

    public static function validateValues(): array
    {
        return array_map(
            fn(self $case) => $case->value,
            array_filter(self::cases(), fn(self $case) => $case->isValidated())
        );
    }
}
