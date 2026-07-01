<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\StationCommand;
use App\Services\StationDeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StationDeviceController extends Controller
{
    public function heartbeat(Request $request, Station $station, StationDeviceService $stationDeviceService): JsonResponse
    {
        $this->ensureAuthorized($request, $station);

        $data = $request->validate([
            'device_status' => ['nullable', 'string'],
            'app_mode' => ['nullable', 'string'],
            'current_screen' => ['nullable', 'string'],
            'device_version' => ['nullable', 'string'],
        ]);

        $station = $stationDeviceService->recordHeartbeat($station, $data);

        return response()->json([
            'ok' => true,
            'station' => [
                'code' => $station->code,
                'device_status' => $station->device_status,
                'app_mode' => $station->app_mode,
                'current_screen' => $station->current_screen,
                'last_heartbeat_at' => optional($station->last_heartbeat_at)->toIso8601String(),
            ],
        ]);
    }

    public function nextCommand(Request $request, Station $station, StationDeviceService $stationDeviceService): JsonResponse
    {
        $this->ensureAuthorized($request, $station);

        $command = $stationDeviceService->nextQueuedCommand($station);

        return response()->json([
            'ok' => true,
            'command' => $command ? [
                'id' => $command->id,
                'type' => $command->type,
                'payload' => $command->payload,
                'sent_at' => optional($command->sent_at)->toIso8601String(),
            ] : null,
        ]);
    }

    public function acknowledge(Request $request, Station $station, StationCommand $command, StationDeviceService $stationDeviceService): JsonResponse
    {
        $this->ensureAuthorized($request, $station);
        abort_unless($command->station_id === $station->id, 404);

        $data = $request->validate([
            'success' => ['required', 'boolean'],
            'failure_message' => ['nullable', 'string'],
        ]);

        $command = $stationDeviceService->acknowledgeCommand(
            command: $command,
            success: (bool) $data['success'],
            failureMessage: $data['failure_message'] ?? null,
        );

        return response()->json([
            'ok' => true,
            'status' => $command->status,
        ]);
    }

    protected function ensureAuthorized(Request $request, Station $station): void
    {
        abort_unless(
            hash_equals((string) $station->device_token, (string) $request->header('X-Station-Token')),
            401,
        );
    }
}
