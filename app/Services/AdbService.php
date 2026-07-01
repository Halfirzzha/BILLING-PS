<?php

namespace App\Services;

use App\Enums\StationCommandType;
use App\Models\Station;
use App\Models\StationCommand;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class AdbService
{
    public function execute(StationCommand $command): void
    {
        $station = $command->station;

        if (! $station->adb_identifier) {
            throw new RuntimeException("Station {$station->name} has no adb identifier.");
        }

        match ($command->type) {
            StationCommandType::WakeDevice->value => $this->run($station, ['shell', 'input', 'keyevent', 'KEYCODE_WAKEUP']),
            StationCommandType::RestartBrowser->value => $this->restartBrowser($station),
            StationCommandType::ShowQr->value,
            StationCommandType::ShowStationScreen->value,
            StationCommandType::SessionStarted->value,
            StationCommandType::SessionEnded->value => $this->openUrl($station, $command->payload['station_url'] ?? route('stations.display', $station)),
            StationCommandType::OpenUrl->value => $this->openUrl($station, $command->payload['url'] ?? null),
            default => throw new RuntimeException("Unsupported station command [{$command->type}]."),
        };
    }

    public function openUrl(Station $station, ?string $url): void
    {
        if (! $url) {
            throw new RuntimeException('No URL provided for OpenUrl command.');
        }

        $this->run($station, [
            'shell',
            'am',
            'start',
            '-a',
            'android.intent.action.VIEW',
            '-d',
            $url,
        ]);
    }

    public function restartBrowser(Station $station): void
    {
        $package = config('adb.browser_package', 'com.android.chrome');

        $this->run($station, ['shell', 'am', 'force-stop', $package]);
        $this->openUrl($station, route('stations.display', $station));
    }

    protected function run(Station $station, array $arguments): void
    {
        if (! config('adb.enabled')) {
            throw new RuntimeException('ADB is disabled by configuration.');
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
