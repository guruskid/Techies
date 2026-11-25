<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Event;
use App\Models\Hub;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicSearchController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'min:2', 'max:100'],
        ]);
        $search = trim($data['q'] ?? '');
        $members = collect();
        $hubs = collect();
        $events = collect();
        $opportunities = collect();
        $articles = collect();

        if ($search !== '') {
            $members = User::where('status', 'active')
                ->whereHas('profile', fn ($query) => $query->where('is_public', true))
                ->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhereHas('profile', fn ($profile) => $profile
                            ->where('professional_title', 'like', "%{$search}%")
                            ->orWhere('technology_category', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%"));
                })
                ->with('profile')
                ->limit(6)
                ->get();

            $hubs = Hub::where('is_public', true)
                ->where('status', 'approved')
                ->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%");
                })
                ->latest()
                ->limit(6)
                ->get();

            $events = Event::with('category')
                ->where('status', 'published')
                ->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                })
                ->orderByDesc('start_date')
                ->limit(6)
                ->get();

            $opportunities = Opportunity::with('category')
                ->where('status', 'published')
                ->when(! $request->user()?->hasActiveMembership(), fn ($query) => $query->where('is_member_only', false))
                ->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('organization', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                })
                ->latest()
                ->limit(6)
                ->get();

            $articles = Article::approved()
                ->with('author')
                ->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                })
                ->latest('published_at')
                ->limit(6)
                ->get();
        }

        return view('pages.search', compact('search', 'members', 'hubs', 'events', 'opportunities', 'articles'));
    }
}
