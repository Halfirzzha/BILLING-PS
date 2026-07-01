<?php

namespace App\Enums;

enum StationCommandStatus: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Acknowledged = 'acknowledged';
    case Processed = 'processed';
    case Failed = 'failed';
}
