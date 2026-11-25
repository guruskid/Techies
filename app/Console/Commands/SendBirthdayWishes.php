<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\BirthdayGreeting;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class SendBirthdayWishes extends Command
{
    protected $signature = 'members:send-birthday-wishes';

    protected $description = 'Email active members on their birthday';

    public function handle(): int
    {
        $today = today();
        $month = (int) $today->month;
        $day = (int) $today->day;

        $members = User::with('profile')
            ->where('status', 'active')
            ->whereIn('membership_status', ['active', 'exempt'])
            ->where(function (Builder $query) use ($today): void {
                $query->whereNull('birthday_wished_at')
                    ->orWhereYear('birthday_wished_at', '<', $today->year);
            })
            ->whereHas('profile', function (Builder $query) use ($month, $day, $today): void {
                $query->where(function (Builder $birthday) use ($month, $day, $today): void {
                    $birthday->whereMonth('date_of_birth', $month)
                        ->whereDay('date_of_birth', $day);

                    // People born on 29 February receive the message on 28 February in non-leap years.
                    if ($month === 2 && $day === 28 && ! $today->isLeapYear()) {
                        $birthday->orWhere(function (Builder $leap): void {
                            $leap->whereMonth('date_of_birth', 2)->whereDay('date_of_birth', 29);
                        });
                    }
                });
            })
            ->get();

        $sent = 0;

        foreach ($members as $member) {
            $preferences = $member->profile?->notification_preferences;

            if (is_array($preferences) && array_key_exists('community', $preferences) && ! $preferences['community']) {
                continue;
            }

            $member->notify(new BirthdayGreeting);
            $member->forceFill(['birthday_wished_at' => now()])->save();
            $sent++;
        }

        $this->info('Sent '.$sent.' birthday message(s).');

        return self::SUCCESS;
    }
}
