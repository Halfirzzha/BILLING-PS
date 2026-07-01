<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Wallet = 'wallet';
    case Cash = 'cash';
    case Transfer = 'transfer';
    case TimeBalance = 'time_balance';
}
