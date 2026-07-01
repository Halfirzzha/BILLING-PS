<?php

namespace App\Console\Commands;

use App\Services\SessionService;
use Illuminate\Console\Command;

class AutoStopSessions extends Command
{
    protected $signature = 'sessions:auto-stop';

    protected $description = 'End play sessions whose planned time has elapsed (auto-stop backstop).';

    public function handle(SessionService $sessions): int
    {
        $count = $sessions->stopExpiredSessions();

        $this->info("Auto-stopped {$count} expired session(s).");

        return self::SUCCESS;
    }
}
