<?php

namespace App\Services;

use App\Enums\StationCommandType;
use App\Models\Station;
use App\Models\StationCommand;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class AdbService
{
    /**
     * Device-level command types handled by the local ADB agent
     * (rendering is done by the native TV app, so no URL opening here).
     */
    public const ADB_TYPES = [
        StationCommandType::Wake->value,
        StationCommandType::RelaunchApp->value,
        StationCommandType::Reboot->value,
        StationCommandType::CustomAdb->value,
    ];

    public function execute(StationCommand $command): void
    {
        $station = $command->station;

        if (! $station->adb_identifier) {
            throw new RuntimeException("Station {$station->name} has no adb identifier.");
        }

        match ($command->type) {
            StationCommandType::Wake => $this->run($station, ['shell', 'input', 'keyevent', 'KEYCODE_WAKEUP']),
            StationCommandType::Reboot => $this->run($station, ['reboot']),
            StationCommandType::RelaunchApp => $this->relaunchApp($station),
            StationCommandType::CustomAdb => $this->run($station, array_values((array) ($command->payload['args'] ?? []))),
            default => throw new RuntimeException("Command [{$command->type->value}] is not handled by the ADB agent."),
        };
    }

    private function relaunchApp(Station $station): void
    {
        $package = config('adb.app_package');

        $this->run($station, ['shell', 'am', 'force-stop', $package]);
        $this->run($station, ['shell', 'monkey', '-p', $package, '-c', 'android.intent.category.LAUNCHER', '1']);
    }

    private function run(Station $station, array $arguments): void
    {
        if (! config('adb.enabled')) {
            throw new RuntimeException('ADB is disabled by configuration.');
        }

        if ($arguments === []) {
            throw new RuntimeException('No ADB arguments provided.');
        }

        $result = Process::run(array_merge([
            config('adb.binary', 'adb'),
            '-s',
            $station->adb_identifier,
        ], $arguments));

        if ($result->failed()) {
            throw new RuntimeException(trim($result->errorOutput()) ?: trim($result->output()) ?: 'ADB command failed.');
        }
    }
}
