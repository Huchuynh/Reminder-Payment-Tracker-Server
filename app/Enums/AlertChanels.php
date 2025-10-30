<?php

namespace App\Enums;

enum AlertChanels: string
{
    case EMAIL = "email";
    case IN_APP = "in_app";
    case PUSH = "push";
}
