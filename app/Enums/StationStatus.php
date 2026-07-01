<?php

namespace App\Enums;

enum StationStatus: string
{
    case Idle = 'idle';
    case Active = 'active';
    case Maintenance = 'maintenance';
}
