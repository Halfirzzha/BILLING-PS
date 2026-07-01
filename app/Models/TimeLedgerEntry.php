<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOutlet;
use Database\Factories\TimeLedgerEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'outlet_id', 'operator_id', 'time_package_id', 'play_session_id', 'type', 'minutes', 'notes', 'meta'])]
class TimeLedgerEntry extends Model
{
    /** @use HasFactory<TimeLedgerEntryFactory> */
    use HasFactory;
    use BelongsToOutlet;

    protected function casts(): array
    {
        return [
            'minutes' => 'integer',
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

    public function timePackage(): BelongsTo
    {
        return $this->belongsTo(TimePackage::class);
    }

    public function playSession(): BelongsTo
    {
        return $this->belongsTo(PlaySession::class);
    }
}
