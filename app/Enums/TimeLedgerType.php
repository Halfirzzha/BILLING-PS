<?php

namespace App\Enums;

enum TimeLedgerType: string
{
    case Credit = 'credit';
    case SessionDebit = 'session_debit';
    case Adjustment = 'adjustment';
}
