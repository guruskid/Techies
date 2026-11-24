<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Chapter;
use App\Models\Event;
use App\Models\Hub;
use App\Models\MemberFeedback;
use App\Models\MentorshipRequest;
use App\Models\Opportunity;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'members' => User::where('role', 'member')->count(),
            'hubs' => Hub::count(),
            'pending_hubs' => Hub::where('status', 'pending')->count(),
            'events' => Event::count(),
            'opportunities' => Opportunity::count(),
            'chapters' => Chapter::count(),
            'pending_content' => Article::pending()->count(),
            'published_content' => Article::approved()->count(),
            'active_memberships' => User::where('role', 'member')->where('membership_status', 'active')->count(),
            'pending_memberships' => User::where('role', 'member')->whereIn('membership_status', ['unpaid', 'pending'])->count(),
            'mentorship_pending' => MentorshipRequest::where('status', 'pending')->count(),
            'feedback_average' => round((float) MemberFeedback::avg('rating'), 1),
        ];

        $pendingArticles = Article::pending()
            ->with('author')
            ->orderBy('submitted_at')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact('stats', 'pendingArticles'));
    }
}
