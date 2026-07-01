<?php

namespace App\Enums;

enum WalletTransactionType: string
{
    case TopUp = 'top_up';
    case TimePurchase = 'time_purchase';
    case CashSale = 'cash_sale';
    case Adjustment = 'adjustment';
    case Refund = 'refund';
}
