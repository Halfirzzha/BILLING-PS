<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Wallet = 'wallet';
    case Gateway = 'gateway';
    case TimeBalance = 'time_balance';
}
