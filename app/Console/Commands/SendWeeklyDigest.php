<?php

namespace App\Console\Commands;

use App\Mail\WeeklyDigest;
use App\Models\Article;
use App\Models\Event;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyDigest extends Command
{
    protected $signature = 'community:send-digest {--dry-run : Count recipients without sending emails}';

    protected $description = 'Email the weekly community digest to members who opted in';

    public function handle(): int
    {
        $events = Event::where('status', 'published')
            ->whereDate('start_date', '>=', today())
            ->whereDate('start_date', '<=', today()->addDays(7))
            ->orderBy('start_date')
            ->get();

        $articles = Article::approved()
            ->with('author')
            ->where('published_at', '>=', now()->subDays(7))
            ->orderByDesc('published_at')
            ->take(5)
            ->get();

        $opportunities = Opportunity::where('status', 'published')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        if ($events->isEmpty() && $articles->isEmpty() && $opportunities->isEmpty()) {
            $this->info('Nothing to include this week, so no digest was sent.');

            return self::SUCCESS;
        }

        $recipients = User::where('status', 'active')
            ->whereHas('profile', fn ($query) => $query->where('weekly_digest', true))
            ->get();

        if ($this->option('dry-run')) {
            $this->info("{$recipients->count()} member(s) would receive the weekly digest.");

            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($recipients as $member) {
            Mail::to($member->email)->send(new WeeklyDigest($member, $events, $articles, $opportunities));
            $sent++;
        }

        $this->info("Sent the weekly digest to {$sent} member(s).");

        return self::SUCCESS;
    }
}
