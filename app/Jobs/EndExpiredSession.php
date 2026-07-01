<?php

namespace App\Jobs;

use App\Enums\PlaySessionStatus;
use App\Models\PlaySession;
use App\Services\SessionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EndExpiredSession implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $sessionId) {}

    public function handle(SessionService $sessions): void
    {
        $session = PlaySession::find($this->sessionId);

        if (! $session || $session->status !== PlaySessionStatus::Active) {
            return;
        }

        // Skip if the planned end was pushed back (e.g. time extended); the
        // scheduled sweep or a freshly dispatched job will handle it later.
        if ($session->planned_end_at && $session->planned_end_at->isFuture()) {
            return;
        }

        $sessions->endSession($session);
    }
}
