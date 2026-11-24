<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Article;
use App\Models\Chapter;
use App\Models\Event;
use App\Models\Hub;
use App\Models\Opportunity;
use App\Models\SpeakerSubmission;
use App\Models\User;
use App\Models\UserProfile;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('super-admin.dashboard');
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $profile = $user->profile;
        $completion = 0;
        $missing = [];
        if ($profile) {
            $completion = $profile->completionPercentage();
            $missing = $profile->missingCompletionFields();
        } else {
            $missing = array_values(UserProfile::completionFields());
        }
        $upcomingEvents = Event::where('status', 'published')->where('start_date', '>=', now()->toDateString())->latest()->take(5)->get();
        $recommendedHubs = Hub::where('is_verified', true)->latest()->take(4)->get();
        $hub = $user->hub;
        $coordinatedChapters = Chapter::where('coordinator_id', $user->id)
            ->orWhere('second_coordinator_id', $user->id)
            ->orderBy('name')
            ->get();
        $recentMembers = User::where('status', 'active')
            ->whereHas('profile', fn ($q) => $q->where('is_public', true))
            ->where('id', '!=', $user->id)
            ->latest()
            ->take(5)
            ->get();
        $opportunities = Opportunity::where('status', 'published')->latest()->take(5)->get();
        $announcements = Announcement::where('is_published', true)->latest()->take(5)->get();
        $registeredEvents = $user->eventRegistrations()->where('status', 'registered')->count();

        $applicationCount = $user->opportunityApplications()->count();

        $contentStats = [
            'total' => $user->articles()->count(),
            'drafts' => $user->articles()->where('status', Article::STATUS_DRAFT)->count(),
            'pending' => $user->articles()->where('status', Article::STATUS_PENDING)->count(),
            'approved' => $user->articles()->where('status', Article::STATUS_APPROVED)->count(),
            'rejected' => $user->articles()->where('status', Article::STATUS_REJECTED)->count(),
        ];
        $latestArticles = $user->articles()->latest()->take(4)->get();

        $onboarding = [
            ['Complete your profile', ($profile?->completionPercentage() ?? 0) >= 80, route('profile.edit')],
            ['Join a chapter', $user->chapters()->exists(), route('chapters')],
            ['Register for an event', $user->eventRegistrations()->whereIn('status', ['registered', 'waitlisted'])->exists(), route('events')],
            ['Apply to an opportunity or submit a talk', $user->opportunityApplications()->exists() || SpeakerSubmission::where('user_id', $user->id)->exists(), route('opportunities')],
            ['Request a mentor or become one', $user->mentorshipRequest()->exists(), route('mentorship.index')],
        ];

        return view('member.dashboard', compact('user', 'profile', 'completion', 'missing', 'upcomingEvents', 'recommendedHubs', 'recentMembers', 'opportunities', 'announcements', 'registeredEvents', 'contentStats', 'latestArticles', 'applicationCount', 'hub', 'coordinatedChapters', 'onboarding'));
    }
}
