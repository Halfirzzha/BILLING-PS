<?php

namespace App\Enums;

enum StationCommandType: string
{
    case Wake = 'wake';
    case RelaunchApp = 'relaunch_app';
    case Reboot = 'reboot';
    case RefreshState = 'refresh_state';
    case CustomAdb = 'custom_adb';
}
