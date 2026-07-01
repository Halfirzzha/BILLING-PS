<?php

namespace App\Enums;

enum PlaySessionStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
