<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\EventRegistration;
use App\Models\SpeakerSubmission;
use Illuminate\View\View;

class PassportController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();
        $user->load(['profile', 'chapters', 'certificates.event', 'mentorshipRequest.match']);

        $stats = [
            'events' => EventRegistration::where('user_id', $user->id)
                ->whereIn('status', [EventRegistration::STATUS_REGISTERED, EventRegistration::STATUS_WAITLISTED])
                ->count(),
            'talks' => SpeakerSubmission::where('user_id', $user->id)->count(),
            'articles' => Article::where('user_id', $user->id)->where('status', Article::STATUS_APPROVED)->count(),
            'chapters' => $user->chapters->count(),
            'certificates' => $user->certificates->count(),
            'referrals' => $user->referrals()->count(),
        ];

        $steps = [
            ['label' => 'Complete your profile', 'done' => ($user->profile?->completionPercentage() ?? 0) >= 80, 'url' => route('profile.edit')],
            ['label' => 'Join a chapter', 'done' => $user->chapters->isNotEmpty(), 'url' => route('chapters')],
            ['label' => 'Register for an event', 'done' => $stats['events'] > 0, 'url' => route('events')],
            ['label' => 'Apply to an opportunity or submit a talk', 'done' => $stats['talks'] > 0 || $user->opportunityApplications()->exists(), 'url' => route('opportunities')],
            ['label' => 'Request a mentor or become one', 'done' => $user->mentorshipRequest !== null, 'url' => route('mentorship.index')],
        ];

        return view('member.passport', compact('user', 'stats', 'steps'));
    }
}
