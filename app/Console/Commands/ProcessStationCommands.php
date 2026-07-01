<?php

namespace App\Console\Commands;

use App\Enums\StationCommandStatus;
use App\Models\StationCommand;
use App\Services\AdbService;
use Illuminate\Console\Command;
use Throwable;

class ProcessStationCommands extends Command
{
    protected $signature = 'stations:process-commands {--limit=50}';

    protected $description = 'Local ADB agent: execute pending device-level station commands via ADB.';

    public function handle(AdbService $adb): int
    {
        $commands = StationCommand::query()
            ->where('status', StationCommandStatus::Pending->value)
            ->whereIn('type', AdbService::ADB_TYPES)
            ->whereHas('station', fn ($q) => $q->whereNotNull('adb_identifier'))
            ->orderBy('id')
            ->limit((int) $this->option('limit'))
            ->get();

        foreach ($commands as $command) {
            $command->update([
                'status' => StationCommandStatus::Dispatched->value,
                'dispatched_at' => now(),
            ]);

            try {
                $adb->execute($command);
                $command->update([
                    'status' => StationCommandStatus::Acknowledged->value,
                    'acknowledged_at' => now(),
                ]);
            } catch (Throwable $e) {
                $command->update([
                    'status' => StationCommandStatus::Failed->value,
                    'error' => mb_substr($e->getMessage(), 0, 500),
                ]);
                $this->error("Command #{$command->id} failed: {$e->getMessage()}");
            }
        }

        $this->info("Processed {$commands->count()} ADB command(s).");

        return self::SUCCESS;
    }
}
