<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;

class HubDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $hub = $user->hub;

        if (! $hub) {
            return redirect()->route('hubs.register')
                ->with('status', 'Register your hub to access the hub dashboard.');
        }

        $hub->loadCount(['events', 'teamMembers', 'applications']);
        $hub->load('teamMembers.user');

        $stats = [
            'Events' => $hub->events_count,
            'Upcoming events' => $hub->events()->where('start_date', '>=', now()->toDateString())->count(),
            'Team members' => $hub->team_members_count,
        ];

        $recentEvents = $hub->events()->latest('start_date')->take(5)->get();
        $application = $hub->applications()->latest()->first();

        return view('hub.dashboard', compact('hub', 'stats', 'recentEvents', 'application'));
    }

    public function team()
    {
        $user = auth()->user();
        $hub = $user->hub;

        if (! $hub) {
            return redirect()->route('hubs.register')
                ->with('status', 'Register your hub to access the hub dashboard.');
        }

        $hub->load('teamMembers.user');

        return view('hub.team', compact('hub'));
    }
}
