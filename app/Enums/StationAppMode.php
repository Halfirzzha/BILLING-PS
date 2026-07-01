<?php

namespace App\Enums;

enum StationAppMode: string
{
    case Qr = 'qr';
    case Session = 'session';
    case Maintenance = 'maintenance';
}
