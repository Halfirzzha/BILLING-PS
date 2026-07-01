<?php

namespace App\Models;

use App\Enums\RoleName;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'member_code', 'email', 'phone', 'password', 'is_active', 'last_seen_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasRoles;
    use Notifiable;

    protected string $guard_name = 'web';

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (! $user->member_code) {
                $user->member_code = 'USR-' . Str::upper(Str::random(8));
            }
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->hasAnyRole([
            RoleName::Developer->value,
            RoleName::SuperAdmin->value,
            RoleName::Operator->value,
        ]);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function timeLedgerEntries(): HasMany
    {
        return $this->hasMany(TimeLedgerEntry::class);
    }

    public function playSessions(): HasMany
    {
        return $this->hasMany(PlaySession::class);
    }

    public function getWalletBalanceAttribute(): int
    {
        return (int) $this->walletTransactions()->where('affects_balance', true)->sum('amount');
    }

    public function getRemainingMinutesAttribute(): int
    {
        return max(0, (int) $this->timeLedgerEntries()->sum('minutes'));
    }

    public function isMember(): bool
    {
        return $this->hasRole(RoleName::Member->value);
    }
}
