<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-stop backstop: sweep expired sessions every minute (layer 2 of 2;
// layer 1 is the delayed EndExpiredSession job dispatched at start).
Schedule::command('sessions:auto-stop')->everyMinute()->withoutOverlapping();
