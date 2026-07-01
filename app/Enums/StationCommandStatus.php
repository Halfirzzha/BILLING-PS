<?php

namespace App\Enums;

enum StationCommandStatus: string
{
    case Pending = 'pending';
    case Dispatched = 'dispatched';
    case Acknowledged = 'acknowledged';
    case Failed = 'failed';
}
