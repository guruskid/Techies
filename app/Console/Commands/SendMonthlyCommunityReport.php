<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Event;
use App\Models\MemberSpotlight;
use App\Models\Opportunity;
use App\Models\User;
use App\Notifications\MonthlyCommunityReport;
use Illuminate\Console\Command;

class SendMonthlyCommunityReport extends Command
{
    protected $signature = 'community:send-monthly-report';

    protected $description = 'Email active members a short monthly community report';

    public function handle(): int
    {
        $stats = [
            'events' => Event::where('status', 'published')->whereMonth('start_date', now()->month)->count(),
            'opportunities' => Opportunity::where('status', 'published')->count(),
            'articles' => Article::approved()->whereMonth('published_at', now()->month)->count(),
            'members' => User::where('status', 'active')->count(),
        ];

        $spotlight = MemberSpotlight::with('user')
            ->where('is_published', true)
            ->latest('month')
            ->first();

        $message = $spotlight ? $spotlight->quote.' — '.$spotlight->user?->name : null;

        $sent = 0;
        User::where('status', 'active')
            ->whereIn('membership_status', ['active', 'exempt'])
            ->chunkById(200, function ($members) use ($stats, $message, &$sent): void {
                foreach ($members as $member) {
                    $member->notify(new MonthlyCommunityReport($stats, $message));
                    $sent++;
                }
            });

        $this->info('Sent '.$sent.' monthly report email(s).');

        return self::SUCCESS;
    }
}
