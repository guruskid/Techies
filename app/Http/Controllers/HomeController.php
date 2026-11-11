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
            'members' => User::where('role', 'member')->count(),
            'hubs' => Hub::where('is_verified', true)->count(),
            'chapters' => Chapter::count(),
            'events' => Event::count(),
        ];
        $members = User::where('role', 'member')->latest()->take(6)->get();
        $hubs = Hub::where('is_verified', true)->latest()->take(6)->get();
        $events = Event::where('status', 'published')->latest()->take(6)->get();
        $opportunities = Opportunity::where('status', 'published')->latest()->take(6)->get();
        $chapters = Chapter::latest()->take(8)->get();

        return view('home', compact('stats', 'members', 'hubs', 'events', 'opportunities', 'chapters'));
    }
}
