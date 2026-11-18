<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Notifications\EventReminder;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders {--days=1 : How many days ahead to send reminders for}';

    protected $description = 'Email registered attendees about events starting in the given number of days';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $targetDate = today()->addDays($days)->toDateString();

        $sent = 0;

        Event::query()
            ->where('status', 'published')
            ->whereDate('start_date', $targetDate)
            ->with(['registrations' => function ($query): void {
                $query->where('status', EventRegistration::STATUS_REGISTERED)
                    ->whereNull('reminded_at')
                    ->with('user');
            }])
            ->each(function (Event $event) use (&$sent): void {
                foreach ($event->registrations as $registration) {
                    if ($registration->user === null) {
                        continue;
                    }

                    $registration->user->notify(new EventReminder($event));
                    $registration->update(['reminded_at' => now()]);
                    $sent++;
                }
            });

        $this->info("Sent {$sent} event reminder email(s) for events on {$targetDate}.");

        return self::SUCCESS;
    }
}
