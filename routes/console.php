<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('events:send-reminders')->dailyAt('08:00');
Schedule::command('community:send-digest')->weeklyOn(1, '08:00');
Schedule::command('mail:sync')->everyTenMinutes()->withoutOverlapping();
Schedule::command('mail:broadcasts:send-due')->everyMinute()->withoutOverlapping();
