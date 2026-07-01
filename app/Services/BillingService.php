<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PlaySessionStatus;
use App\Enums\RoleName;
use App\Enums\StationStatus;
use App\Enums\TimeLedgerType;
use App\Enums\WalletTransactionType;
use App\Models\PlaySession;
use App\Models\Station;
use App\Models\TimeLedgerEntry;
use App\Models\TimePackage;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class BillingService
{
    public function __construct(
        protected StationDeviceService $stationDeviceService,
    ) {}

    public function ensureMemberRole(User $user): void
    {
        if (! $user->hasRole(RoleName::Member->value)) {
            $user->assignRole(RoleName::Member->value);
        }

        if (! $user->member_code) {
            $user->forceFill([
                'member_code' => $this->generateMemberCode(),
            ])->save();
        }
    }

    public function topUpWallet(User $user, int $amount, PaymentMethod $method, ?User $operator = null, ?string $notes = null): WalletTransaction
    {
        if ($amount <= 0) {
            throw new RuntimeException('Top up amount must be greater than zero.');
        }

        $this->ensureMemberRole($user);

        return WalletTransaction::create([
            'user_id' => $user->id,
            'operator_id' => $operator?->id,
            'type' => WalletTransactionType::TopUp->value,
            'payment_method' => $method->value,
            'amount' => $amount,
            'affects_balance' => true,
            'reference' => 'TOP-' . Str::upper(Str::random(8)),
            'notes' => $notes,
        ]);
    }

    public function purchaseTimePackage(User $user, TimePackage $package, PaymentMethod $method, ?User $operator = null, ?string $notes = null): void
    {
        if (! $package->is_active) {
            throw new RuntimeException('Selected package is inactive.');
        }

        $this->ensureMemberRole($user);

        DB::transaction(function () use ($user, $package, $method, $operator, $notes) {
            if ($method === PaymentMethod::Wallet) {
                if ($user->wallet_balance < $package->price) {
                    throw new RuntimeException('Wallet balance is not enough.');
                }

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'operator_id' => $operator?->id,
                    'type' => WalletTransactionType::TimePurchase->value,
                    'payment_method' => $method->value,
                    'amount' => -$package->price,
                    'affects_balance' => true,
                    'reference' => 'PKG-' . Str::upper(Str::random(8)),
                    'notes' => $notes ?? "Purchase package {$package->name}",
                ]);
            } else {
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'operator_id' => $operator?->id,
                    'type' => WalletTransactionType::CashSale->value,
                    'payment_method' => $method->value,
                    'amount' => $package->price,
                    'affects_balance' => false,
                    'reference' => 'CSH-' . Str::upper(Str::random(8)),
                    'notes' => $notes ?? "Cash sale for package {$package->name}",
                ]);
            }

            TimeLedgerEntry::create([
                'user_id' => $user->id,
                'operator_id' => $operator?->id,
                'time_package_id' => $package->id,
                'type' => TimeLedgerType::Credit->value,
                'minutes' => $package->minutes,
                'notes' => $notes ?? "Time package {$package->name}",
                'meta' => [
                    'payment_method' => $method->value,
                    'price' => $package->price,
                ],
            ]);
        });
    }

    public function startSession(User $user, Station $station, ?string $notes = null): PlaySession
    {
        $this->ensureMemberRole($user);

        if (! $station->is_active) {
            throw new RuntimeException('Station is inactive.');
        }

        if ($station->playSessions()->where('status', PlaySessionStatus::Active->value)->exists()) {
            throw new RuntimeException('Station already has an active session.');
        }

        if ($user->playSessions()->where('status', PlaySessionStatus::Active->value)->exists()) {
            throw new RuntimeException('User already has an active session.');
        }

        if ($user->remaining_minutes <= 0) {
            throw new RuntimeException('User has no time balance.');
        }

        return DB::transaction(function () use ($user, $station, $notes) {
            $session = PlaySession::create([
                'user_id' => $user->id,
                'station_id' => $station->id,
                'status' => PlaySessionStatus::Active->value,
                'payment_method' => PaymentMethod::TimeBalance->value,
                'started_with_minutes' => $user->remaining_minutes,
                'started_at' => now(),
                'notes' => $notes,
            ]);

            $station->update([
                'status' => StationStatus::Active->value,
                'app_mode' => 'active_session',
            ]);

            $this->stationDeviceService->syncStationPresentation($station);

            return $session;
        });
    }

    public function endSession(PlaySession $session, ?User $operator = null): PlaySession
    {
        if ($session->status !== PlaySessionStatus::Active->value) {
            throw new RuntimeException('Session is no longer active.');
        }

        return DB::transaction(function () use ($session, $operator) {
            $session->refresh();

            $consumedMinutes = $session->elapsedMinutes();
            $availableMinutes = $session->user->remaining_minutes;
            $minutesToDebit = min($consumedMinutes, $availableMinutes);
            $overageMinutes = max(0, $consumedMinutes - $availableMinutes);

            TimeLedgerEntry::create([
                'user_id' => $session->user_id,
                'operator_id' => $operator?->id,
                'play_session_id' => $session->id,
                'type' => TimeLedgerType::SessionDebit->value,
                'minutes' => -$minutesToDebit,
                'notes' => "Session usage for station {$session->station->name}",
                'meta' => [
                    'station_id' => $session->station_id,
                ],
            ]);

            $session->update([
                'status' => PlaySessionStatus::Completed->value,
                'consumed_minutes' => $consumedMinutes,
                'overage_minutes' => $overageMinutes,
                'ended_at' => now(),
            ]);

            $session->station->update([
                'status' => StationStatus::Idle->value,
                'app_mode' => 'qr',
            ]);

            $this->stationDeviceService->syncStationPresentation($session->station, $operator);

            return $session->fresh();
        });
    }

    public function generateMemberCode(): string
    {
        do {
            $code = 'MBR-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (User::where('member_code', $code)->exists());

        return $code;
    }
}
