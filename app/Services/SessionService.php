<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PlaySessionStatus;
use App\Enums\StationAppMode;
use App\Enums\StationCommandStatus;
use App\Enums\StationCommandType;
use App\Enums\StationStatus;
use App\Enums\TimeLedgerType;
use App\Events\SessionEnded;
use App\Events\SessionStarted;
use App\Jobs\EndExpiredSession;
use App\Models\PlaySession;
use App\Models\Station;
use App\Models\StationCommand;
use App\Models\TimeLedgerEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SessionService
{
    public function startSession(User $user, Station $station, ?User $operator = null, ?string $notes = null): PlaySession
    {
        if (! $station->is_active || $station->status === StationStatus::Maintenance) {
            throw new RuntimeException('Station is not available.');
        }

        if ($station->current_session_id || $station->playSessions()->where('status', PlaySessionStatus::Active->value)->exists()) {
            throw new RuntimeException('Station already has an active session.');
        }

        if ($user->playSessions()->where('status', PlaySessionStatus::Active->value)->exists()) {
            throw new RuntimeException('User already has an active session.');
        }

        $available = $user->remaining_minutes;

        if ($available <= 0) {
            throw new RuntimeException('User has no time balance.');
        }

        $session = DB::transaction(function () use ($user, $station, $operator, $available, $notes): PlaySession {
            $session = PlaySession::create([
                'outlet_id' => $station->outlet_id,
                'station_id' => $station->id,
                'user_id' => $user->id,
                'status' => PlaySessionStatus::Active->value,
                'payment_method' => PaymentMethod::TimeBalance->value,
                'started_at' => now(),
                'planned_end_at' => now()->addMinutes($available),
                'started_with_minutes' => $available,
                'notes' => $notes,
            ]);

            $station->update([
                'status' => StationStatus::Active->value,
                'app_mode' => StationAppMode::Session->value,
                'current_session_id' => $session->id,
            ]);

            $this->enqueueCommand($station, StationCommandType::RefreshState, [
                'mode' => 'session',
                'session_id' => $session->id,
            ], $operator);

            return $session;
        });

        EndExpiredSession::dispatch($session->id)->delay($session->planned_end_at);
        SessionStarted::dispatch($session);

        return $session;
    }

    public function endSession(PlaySession $session, ?User $operator = null): PlaySession
    {
        if ($session->status !== PlaySessionStatus::Active) {
            throw new RuntimeException('Session is not active.');
        }

        $ended = DB::transaction(function () use ($session, $operator): PlaySession {
            $consumed = $session->elapsedMinutes();
            $available = $session->user->remaining_minutes;
            $debit = min($consumed, $available);

            if ($debit > 0) {
                TimeLedgerEntry::create([
                    'user_id' => $session->user_id,
                    'outlet_id' => $session->outlet_id,
                    'operator_id' => $operator?->id,
                    'play_session_id' => $session->id,
                    'type' => TimeLedgerType::SessionDebit->value,
                    'minutes' => -$debit,
                    'notes' => "Pemakaian sesi station {$session->station->name}",
                ]);
            }

            $session->update([
                'status' => PlaySessionStatus::Completed->value,
                'consumed_minutes' => $consumed,
                'minutes_debited' => $debit,
                'ended_at' => now(),
                'ended_by' => $operator?->id,
            ]);

            $station = $session->station;
            $station->update([
                'status' => StationStatus::Idle->value,
                'app_mode' => StationAppMode::Qr->value,
                'current_session_id' => null,
            ]);

            $this->enqueueCommand($station, StationCommandType::RefreshState, ['mode' => 'qr'], $operator);

            return $session->fresh();
        });

        SessionEnded::dispatch($ended);

        return $ended;
    }

    public function stopExpiredSessions(): int
    {
        $sessions = PlaySession::query()
            ->where('status', PlaySessionStatus::Active->value)
            ->whereNotNull('planned_end_at')
            ->where('planned_end_at', '<=', now())
            ->get();

        foreach ($sessions as $session) {
            $this->endSession($session);
        }

        return $sessions->count();
    }

    private function enqueueCommand(Station $station, StationCommandType $type, array $payload, ?User $operator): void
    {
        StationCommand::create([
            'outlet_id' => $station->outlet_id,
            'station_id' => $station->id,
            'type' => $type->value,
            'status' => StationCommandStatus::Pending->value,
            'payload' => $payload,
            'created_by' => $operator?->id,
        ]);
    }
}
