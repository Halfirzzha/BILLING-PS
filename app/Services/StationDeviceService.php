<?php

namespace App\Services;

use App\Enums\PlaySessionStatus;
use App\Enums\StationAppMode;
use App\Enums\StationStatus;
use App\Models\Station;

class StationDeviceService
{
    /**
     * The presentation state the Android TV app should render.
     */
    public function state(Station $station): array
    {
        $session = $station->status === StationStatus::Active
            ? $station->playSessions()
                ->where('status', PlaySessionStatus::Active->value)
                ->with('user')
                ->latest('started_at')
                ->first()
            : null;

        return [
            'station' => [
                'code' => $station->code,
                'name' => $station->name,
                'status' => $station->status->value,
                'app_mode' => $station->app_mode->value,
            ],
            'mode' => $station->app_mode->value,
            'qr' => $station->app_mode === StationAppMode::Qr ? [
                'join_url' => route('join', $station->qr_token),
            ] : null,
            'session' => $session ? [
                'member' => $session->user->name,
                'member_code' => $session->user->member_code,
                'started_at' => $session->started_at?->toIso8601String(),
                'planned_end_at' => $session->planned_end_at?->toIso8601String(),
                'remaining_minutes' => $session->user->remaining_minutes,
            ] : null,
        ];
    }
}
