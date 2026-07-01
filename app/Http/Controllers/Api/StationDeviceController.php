<?php

namespace App\Http\Controllers\Api;

use App\Enums\StationCommandStatus;
use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\StationCommand;
use App\Services\StationDeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StationDeviceController extends Controller
{
    public function __construct(private readonly StationDeviceService $devices) {}

    public function heartbeat(Station $station): JsonResponse
    {
        $station->update(['last_heartbeat_at' => now()]);

        return response()->json($this->devices->state($station));
    }

    public function state(Station $station): JsonResponse
    {
        return response()->json($this->devices->state($station));
    }

    public function nextCommand(Station $station): JsonResponse
    {
        $command = $station->commands()
            ->where('status', StationCommandStatus::Pending->value)
            ->orderBy('id')
            ->first();

        if (! $command) {
            return response()->json(['command' => null]);
        }

        $command->update([
            'status' => StationCommandStatus::Dispatched->value,
            'dispatched_at' => now(),
        ]);

        return response()->json([
            'command' => [
                'id' => $command->id,
                'type' => $command->type->value,
                'payload' => $command->payload,
            ],
        ]);
    }

    public function acknowledge(Request $request, Station $station, StationCommand $command): JsonResponse
    {
        abort_unless($command->station_id === $station->id, 404);

        $success = $request->boolean('success', true);

        $command->update([
            'status' => $success ? StationCommandStatus::Acknowledged->value : StationCommandStatus::Failed->value,
            'acknowledged_at' => now(),
            'error' => $success ? null : $request->string('error')->toString(),
        ]);

        return response()->json(['ok' => true]);
    }
}
