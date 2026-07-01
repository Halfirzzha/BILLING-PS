<?php

namespace App\Services;

use App\Enums\DeviceStatus;
use App\Enums\StationCommandStatus;
use App\Enums\StationCommandType;
use App\Models\PlaySession;
use App\Models\Station;
use App\Models\StationCommand;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class StationDeviceService
{
    public function queueCommand(
        Station $station,
        StationCommandType $type,
        array $payload = [],
        ?User $requestedBy = null,
    ): StationCommand {
        return $station->commands()->create([
            'requested_by' => $requestedBy?->id,
            'type' => $type->value,
            'status' => StationCommandStatus::Queued->value,
            'payload' => $payload,
        ]);
    }

    public function syncStationPresentation(Station $station, ?User $requestedBy = null): StationCommand
    {
        $station->refresh();
        $session = $station->active_session;

        if ($session instanceof PlaySession) {
            return $this->queueCommand($station, StationCommandType::SessionStarted, [
                'station_code' => $station->code,
                'station_url' => route('stations.display', $station),
                'member_name' => $session->user->name,
                'member_code' => $session->user->member_code,
                'remaining_minutes' => $session->user->remaining_minutes,
                'started_at' => $session->started_at?->toIso8601String(),
            ], $requestedBy);
        }

        return $this->queueCommand($station, StationCommandType::ShowQr, [
            'station_code' => $station->code,
            'station_url' => route('stations.display', $station),
            'join_url' => route('stations.join', $station->qr_token),
        ], $requestedBy);
    }

    public function recordHeartbeat(Station $station, array $data): Station
    {
        $station->update([
            'device_status' => $data['device_status'] ?? DeviceStatus::Online->value,
            'app_mode' => $data['app_mode'] ?? $station->app_mode,
            'current_screen' => $data['current_screen'] ?? $station->current_screen,
            'device_version' => $data['device_version'] ?? $station->device_version,
            'last_heartbeat_at' => now(),
        ]);

        return $station->fresh();
    }

    public function nextQueuedCommand(Station $station): ?StationCommand
    {
        $command = $station->commands()
            ->where('status', StationCommandStatus::Queued->value)
            ->oldest('id')
            ->first();

        if (! $command) {
            return null;
        }

        $command->update([
            'status' => StationCommandStatus::Sent->value,
            'sent_at' => now(),
            'attempts' => $command->attempts + 1,
        ]);

        $station->update([
            'last_command_synced_at' => now(),
        ]);

        return $command->fresh();
    }

    public function acknowledgeCommand(StationCommand $command, bool $success, ?string $failureMessage = null): StationCommand
    {
        $command->update([
            'status' => $success ? StationCommandStatus::Processed->value : StationCommandStatus::Failed->value,
            'acknowledged_at' => now(),
            'processed_at' => now(),
            'failure_message' => $failureMessage,
        ]);

        return $command->fresh();
    }

    public function buildStationUrl(Station $station): string
    {
        return route('stations.display', $station);
    }

    public function buildPublicUrl(string $path): string
    {
        return URL::to($path);
    }
}
