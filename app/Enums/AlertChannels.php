<?php

namespace App\Enums;

enum AlertChannels: string
{
    case EMAIL = "email";
    case NOTIFICATION = "notification";

    public function isValidated(): bool
    {
        return match ($this) {
            self::EMAIL, self::NOTIFICATION => true,
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
