<?php

namespace App\Enums;

enum AlertChannels: string
{
    case EMAIL = "email";
    case NOTIFICATION = "notification";
}
