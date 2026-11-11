<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Hub;
use App\Models\Opportunity;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $profile = $user->profile;
        $completion = 0;
        $missing = [];
        if ($profile) {
            $labels = ['first_name' => 'First name', 'last_name' => 'Last name', 'phone' => 'Phone', 'professional_title' => 'Professional title', 'technology_category' => 'Technology category', 'experience_level' => 'Experience level', 'current_organization' => 'Current organization', 'education' => 'Education', 'bio' => 'Bio', 'country' => 'Country', 'state' => 'State', 'city' => 'City', 'linkedin' => 'LinkedIn', 'github' => 'GitHub', 'portfolio' => 'Portfolio', 'website' => 'Website'];
            $filled = 0;
            foreach ($labels as $f => $label) {
                if (! empty($profile->$f)) {
                    $filled++;
                } else {
                    $missing[] = $label;
                }
            }
            $completion = (int) round(($filled / count($labels)) * 100);
        } else {
            $missing = array_values(['First name', 'Last name', 'Phone', 'Professional title', 'Technology category', 'Experience level', 'Current organization', 'Education', 'Bio', 'Country', 'State', 'City', 'LinkedIn', 'GitHub', 'Portfolio', 'Website']);
        }
        $upcomingEvents = Event::where('status', 'published')->where('start_date', '>=', now()->toDateString())->latest()->take(5)->get();
        $recommendedHubs = Hub::where('is_verified', true)->latest()->take(4)->get();
        $recentMembers = User::where('role', 'member')->where('id', '!=', $user->id)->latest()->take(5)->get();
        $opportunities = Opportunity::where('status', 'published')->latest()->take(5)->get();
        $announcements = Announcement::where('is_published', true)->latest()->take(5)->get();

        return view('member.dashboard', compact('user', 'profile', 'completion', 'missing', 'upcomingEvents', 'recommendedHubs', 'recentMembers', 'opportunities', 'announcements'));
    }
}
