<?php

namespace App\Console\Commands;

use App\Models\Opportunity;
use App\Models\User;
use App\Notifications\OpportunityMatches;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendOpportunityMatches extends Command
{
    protected $signature = 'opportunities:send-matches';

    protected $description = 'Email weekly opportunity matches based on member skills and location';

    public function handle(): int
    {
        $opportunities = Opportunity::with(['category', 'hub'])
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('deadline')->orWhereDate('deadline', '>=', today());
            })
            ->get();

        if ($opportunities->isEmpty()) {
            $this->info('No open opportunities to match.');

            return self::SUCCESS;
        }

        $sent = 0;

        User::with(['profile', 'skills'])
            ->where('status', 'active')
            ->whereIn('membership_status', ['active', 'exempt'])
            ->whereHas('profile', fn ($query) => $query->where('weekly_digest', true))
            ->chunkById(100, function ($members) use ($opportunities, &$sent): void {
                foreach ($members as $member) {
                    $applied = $member->opportunityApplications()->pluck('opportunity_id')->all();

                    $matches = $opportunities
                        ->reject(fn (Opportunity $opportunity) => in_array($opportunity->id, $applied, true))
                        ->map(fn (Opportunity $opportunity) => [
                            'opportunity' => $opportunity,
                            'score' => $this->score($member, $opportunity),
                        ])
                        ->filter(fn (array $match) => $match['score'] >= 2)
                        ->sortByDesc('score')
                        ->take(3)
                        ->pluck('opportunity');

                    if ($matches->isEmpty()) {
                        continue;
                    }

                    $member->notify(new OpportunityMatches($matches));
                    $member->forceFill(['opportunity_match_sent_at' => now()])->save();
                    $sent++;
                }
            });

        $this->info('Sent '.$sent.' opportunity match email(s).');

        return self::SUCCESS;
    }

    private function score(User $member, Opportunity $opportunity): int
    {
        $profile = $member->profile;
        $score = 0;

        if ($profile?->state && $opportunity->hub?->state === $profile->state) {
            $score += 4;
        }

        if ($profile?->state && Str::contains(Str::lower((string) $opportunity->location), Str::lower($profile->state))) {
            $score += 3;
        }

        $haystack = Str::lower($opportunity->title.' '.$opportunity->description.' '.$opportunity->category?->name);

        foreach ($member->skills as $skill) {
            if (Str::contains($haystack, Str::lower($skill->name))) {
                $score += 2;
            }
        }

        if ($profile?->technology_category && Str::contains($haystack, Str::lower($profile->technology_category))) {
            $score += 2;
        }

        if ($opportunity->is_remote) {
            $score += 1;
        }

        return $score;
    }
}
