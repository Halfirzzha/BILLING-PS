<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\RoleName;
use App\Enums\TimeLedgerType;
use App\Enums\WalletTransactionType;
use App\Models\TimeLedgerEntry;
use App\Models\TimePackage;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;

class BillingService
{
    public function ensureMember(User $user): void
    {
        if (! $user->hasRole(RoleName::Member->value)) {
            Role::findOrCreate(RoleName::Member->value, 'web');
            $user->assignRole(RoleName::Member->value);
        }
    }

    public function topUpWallet(
        User $user,
        int $amount,
        PaymentMethod $method = PaymentMethod::Cash,
        ?User $operator = null,
        ?int $outletId = null,
        ?string $notes = null,
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new RuntimeException('Top up amount must be greater than zero.');
        }

        $this->ensureMember($user);

        return WalletTransaction::create([
            'user_id' => $user->id,
            'outlet_id' => $outletId ?? $operator?->outlet_id,
            'operator_id' => $operator?->id,
            'type' => WalletTransactionType::TopUp->value,
            'payment_method' => $method->value,
            'amount' => $amount,
            'affects_balance' => true,
            'reference' => 'TOP-'.Str::upper(Str::random(10)),
            'notes' => $notes,
        ]);
    }

    public function purchaseTimePackage(
        User $user,
        TimePackage $package,
        PaymentMethod $method,
        ?User $operator = null,
        ?string $notes = null,
    ): TimeLedgerEntry {
        if (! $package->is_active) {
            throw new RuntimeException('Selected package is inactive.');
        }

        $this->ensureMember($user);

        return DB::transaction(function () use ($user, $package, $method, $operator, $notes): TimeLedgerEntry {
            if ($method === PaymentMethod::Wallet) {
                if ($user->wallet_balance < $package->price) {
                    throw new RuntimeException('Wallet balance is not enough.');
                }

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'outlet_id' => $package->outlet_id,
                    'operator_id' => $operator?->id,
                    'type' => WalletTransactionType::TimePurchase->value,
                    'payment_method' => PaymentMethod::Wallet->value,
                    'amount' => -$package->price,
                    'affects_balance' => true,
                    'reference' => 'PKG-'.Str::upper(Str::random(10)),
                    'notes' => $notes ?? "Beli paket {$package->name}",
                ]);
            } else {
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'outlet_id' => $package->outlet_id,
                    'operator_id' => $operator?->id,
                    'type' => WalletTransactionType::CashSale->value,
                    'payment_method' => $method->value,
                    'amount' => $package->price,
                    'affects_balance' => false,
                    'reference' => 'CSH-'.Str::upper(Str::random(10)),
                    'notes' => $notes ?? "Penjualan cash paket {$package->name}",
                ]);
            }

            return TimeLedgerEntry::create([
                'user_id' => $user->id,
                'outlet_id' => $package->outlet_id,
                'operator_id' => $operator?->id,
                'time_package_id' => $package->id,
                'type' => TimeLedgerType::Credit->value,
                'minutes' => $package->minutes,
                'notes' => $notes ?? "Kredit waktu paket {$package->name}",
                'meta' => [
                    'payment_method' => $method->value,
                    'price' => $package->price,
                ],
            ]);
        });
    }
}
