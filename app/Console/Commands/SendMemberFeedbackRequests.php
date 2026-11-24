<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\MembershipFeedbackRequest;
use Illuminate\Console\Command;

class SendMemberFeedbackRequests extends Command
{
    protected $signature = 'members:request-feedback';

    protected $description = 'Ask members for feedback 30 days after their membership became active';

    public function handle(): int
    {
        $members = User::whereIn('membership_status', ['active', 'exempt'])
            ->where('status', 'active')
            ->whereNotNull('membership_paid_at')
            ->where('membership_paid_at', '<=', now()->subDays(30))
            ->whereNull('feedback_requested_at')
            ->whereDoesntHave('feedback')
            ->get();

        foreach ($members as $member) {
            $member->notify(new MembershipFeedbackRequest);
            $member->forceFill(['feedback_requested_at' => now()])->save();
        }

        $this->info('Sent '.$members->count().' feedback request(s).');

        return self::SUCCESS;
    }
}
