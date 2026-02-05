<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Send pending records to partner every 15 minutes
// The command itself checks for time restrictions (first/last 2 days, 11pm-7am)
Schedule::command('partner:send-pending --limit=50')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();
