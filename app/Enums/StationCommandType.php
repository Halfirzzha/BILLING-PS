<?php

namespace App\Enums;

enum StationCommandType: string
{
    case ShowQr = 'show_qr';
    case ShowStationScreen = 'show_station_screen';
    case SessionStarted = 'session_started';
    case SessionEnded = 'session_ended';
    case WakeDevice = 'wake_device';
    case RestartBrowser = 'restart_browser';
    case OpenUrl = 'open_url';
}
