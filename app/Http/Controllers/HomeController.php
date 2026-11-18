<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Event;
use App\Models\Hub;
use App\Models\Opportunity;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'members' => User::where('status', 'active')->count(),
            'hubs' => Hub::where('status', 'approved')->where('is_verified', true)->count(),
            'chapters' => Chapter::where('status', 'active')->count(),
            'events' => Event::where('status', 'published')->count(),
        ];
        $members = User::where('status', 'active')
            ->whereHas('profile', fn ($q) => $q->where('is_public', true))
            ->latest()
            ->take(6)
            ->get();
        $hubs = Hub::where('status', 'approved')->where('is_verified', true)->latest()->take(6)->get();
        $events = Event::where('status', 'published')->where('start_date', '>=', now()->toDateString())->orderBy('start_date')->take(6)->get();
        $opportunities = Opportunity::where('status', 'published')->latest()->take(6)->get();
        $chapters = Chapter::where('status', 'active')->latest()->take(8)->get();

        return view('home', compact('stats', 'members', 'hubs', 'events', 'opportunities', 'chapters'));
    }
}
