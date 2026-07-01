<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOutlet;
use Database\Factories\WalletTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'outlet_id', 'operator_id', 'type', 'payment_method', 'amount', 'affects_balance', 'reference', 'gateway_ref', 'notes', 'meta'])]
class WalletTransaction extends Model
{
    /** @use HasFactory<WalletTransactionFactory> */
    use HasFactory;
    use BelongsToOutlet;

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'affects_balance' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}
