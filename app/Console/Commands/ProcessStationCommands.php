<?php

namespace App\Console\Commands;

use App\Enums\StationCommandStatus;
use App\Models\StationCommand;
use App\Services\AdbService;
use App\Services\StationDeviceService;
use Illuminate\Console\Command;
use Throwable;

class ProcessStationCommands extends Command
{
    protected $signature = 'stations:process-commands {--once : Process only one batch and exit}';

    protected $description = 'Process queued Android TV / ADB station commands.';

    public function handle(AdbService $adbService, StationDeviceService $stationDeviceService): int
    {
        do {
            $processed = false;

            StationCommand::query()
                ->where('status', StationCommandStatus::Queued->value)
                ->oldest('id')
                ->limit(20)
                ->get()
                ->each(function (StationCommand $command) use ($adbService, $stationDeviceService, &$processed): void {
                    $processed = true;

                    try {
                        $command->update([
                            'status' => StationCommandStatus::Sent->value,
                            'sent_at' => now(),
                            'attempts' => $command->attempts + 1,
                        ]);

                        $adbService->execute($command);
                        $stationDeviceService->acknowledgeCommand($command, true);

                        $this->info("Processed command #{$command->id} for {$command->station->code}");
                    } catch (Throwable $throwable) {
                        $stationDeviceService->acknowledgeCommand($command, false, $throwable->getMessage());
                        $this->error("Command #{$command->id} failed: {$throwable->getMessage()}");
                    }
                });

            if ($this->option('once')) {
                break;
            }

            if (! $processed) {
                sleep(2);
            }
        } while (true);

        return self::SUCCESS;
    }
}
