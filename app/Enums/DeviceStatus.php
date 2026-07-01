<?php

namespace App\Enums;

enum DeviceStatus: string
{
    case Offline = 'offline';
    case Online = 'online';
    case Busy = 'busy';
    case Error = 'error';
}
