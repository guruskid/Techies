<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Chapter;
use App\Models\Event;
use App\Models\Hub;
use App\Models\MemberSpotlight;
use App\Models\Opportunity;
use App\Models\SpeakerCall;
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
        $articles = Article::approved()
            ->with('author')
            ->latest('published_at')
            ->take(3)
            ->get();
        $spotlight = MemberSpotlight::with('user.profile')->where('is_published', true)->latest('month')->first();

        $speakerCalls = SpeakerCall::with('event')
            ->where('status', SpeakerCall::STATUS_OPEN)
            ->where(function ($query): void {
                $query->whereNull('opens_at')->orWhereDate('opens_at', '<=', today());
            })
            ->where(function ($query): void {
                $query->whereNull('closes_at')->orWhereDate('closes_at', '>=', today());
            })
            ->latest()
            ->take(2)
            ->get();

        return view('home', compact(
            'stats', 'members', 'hubs', 'events', 'opportunities', 'chapters', 'articles', 'speakerCalls', 'spotlight'
        ));
    }
}
