<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('events:send-reminders')->dailyAt('08:00');
Schedule::command('community:send-digest')->weeklyOn(1, '08:00');
Schedule::command('members:request-feedback')->dailyAt('09:00');
Schedule::command('members:send-birthday-wishes')->dailyAt('07:30');
Schedule::command('opportunities:send-matches')->weeklyOn(1, '09:00');
Schedule::command('community:send-monthly-report')->monthlyOn(1, '08:00');
Schedule::command('mail:sync')->everyTenMinutes()->withoutOverlapping();
Schedule::command('mail:broadcasts:send-due')->everyMinute()->withoutOverlapping();
Schedule::call(fn () => Cache::put('operations.scheduler_heartbeat', now()->toIso8601String(), now()->addMinutes(5)))
    ->everyMinute()
    ->name('operations-scheduler-heartbeat')
    ->withoutOverlapping();
