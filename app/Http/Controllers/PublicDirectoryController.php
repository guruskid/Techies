<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Article;
use App\Models\Chapter;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Hub;
use App\Models\Opportunity;
use App\Models\OpportunityCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicDirectoryController extends Controller
{
    public function members(Request $request): View
    {
        $query = User::where('status', 'active')
            ->whereHas('profile', fn ($profile) => $profile->where('is_public', true))
            ->with('profile');
        $search = $request->query('search');

        if (is_string($search) && $search !== '') {
            $query->where(function ($users) use ($search): void {
                $users->where('name', 'like', "%{$search}%")
                    ->orWhereHas('profile', fn ($profile) => $profile
                        ->where('professional_title', 'like', "%{$search}%")
                        ->orWhere('technology_category', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%"))
                    ->orWhereHas('skills', fn ($skills) => $skills->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('interests', fn ($interests) => $interests->where('name', 'like', "%{$search}%"));
            });
        }

        $filters = [];

        foreach (['technology' => 'technology_category', 'state' => 'state', 'experience' => 'experience_level'] as $input => $column) {
            $value = $request->query($input);
            $filters[$input] = is_string($value) ? $value : '';

            if (is_string($value) && $value !== '') {
                $query->whereHas('profile', fn ($profile) => $profile->where($column, $value));
            }
        }

        $openTo = $request->query('open_to');
        $openTo = is_string($openTo) ? $openTo : '';

        if (is_string($openTo) && in_array($openTo, ['open_to_work', 'open_to_mentor', 'open_to_collaborate', 'available_for_speaking', 'hiring'], true)) {
            $query->whereHas('profile', fn ($profile) => $profile->where($openTo, true));
        }

        return view('pages.members', [
            'members' => $query->latest()->paginate(12)->withQueryString(),
            'search' => is_string($search) ? $search : '',
            'technology' => $filters['technology'],
            'state' => $filters['state'],
            'experience' => $filters['experience'],
            'openTo' => $openTo,
        ]);
    }

    public function hubs(Request $request): View
    {
        $query = Hub::where('is_public', true)->where('status', 'approved');
        $search = $request->query('search');

        if (is_string($search) && $search !== '') {
            $query->where(function ($hubs) use ($search): void {
                $hubs->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%");
            });
        }

        $state = $request->query('state');
        $organizationType = $request->query('organization_type');
        $state = is_string($state) ? $state : '';
        $organizationType = is_string($organizationType) ? $organizationType : '';

        if ($state !== '') {
            $query->where('state', $state);
        }

        if ($organizationType !== '') {
            $query->where('organization_type', $organizationType);
        }

        $organizationTypes = Hub::query()
            ->where('is_public', true)
            ->where('status', 'approved')
            ->whereNotNull('organization_type')
            ->where('organization_type', '!=', '')
            ->distinct()
            ->orderBy('organization_type')
            ->pluck('organization_type');

        return view('pages.hubs', [
            'hubs' => $query->latest()->paginate(12)->withQueryString(),
            'search' => is_string($search) ? $search : '',
            'state' => $state,
            'organizationType' => $organizationType,
            'organizationTypes' => $organizationTypes,
        ]);
    }

    public function events(Request $request): View
    {
        $query = Event::with('category')
            ->where('status', 'published')
            ->where('start_date', '>=', now()->toDateString());
        $search = $request->query('search');

        if (is_string($search) && $search !== '') {
            $query->where(function ($events) use ($search): void {
                $events->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        foreach (['category' => 'category_id', 'state' => 'state'] as $input => $column) {
            $value = $request->query($input);

            if (is_string($value) && $value !== '') {
                $query->where($column, $value);
            }
        }

        $type = $request->query('type');

        if (is_string($type) && $type !== '') {
            $query->where('is_online', $type === 'online');
        }

        $category = $request->query('category');
        $state = $request->query('state');
        $category = is_string($category) ? $category : '';
        $state = is_string($state) ? $state : '';
        $type = is_string($type) ? $type : '';
        $bookmarkedEventIds = $request->user()
            ? $request->user()->bookmarks()->where('bookmarkable_type', Event::class)->pluck('bookmarkable_id')->map(fn ($id) => (int) $id)->all()
            : [];

        return view('pages.events', [
            'events' => $query->orderBy('start_date')->paginate(12)->withQueryString(),
            'search' => is_string($search) ? $search : '',
            'category' => $category,
            'state' => $state,
            'type' => $type,
            'categories' => EventCategory::all(),
            'bookmarkedEventIds' => $bookmarkedEventIds,
        ]);
    }

    public function opportunities(Request $request): View
    {
        $query = Opportunity::with('category')->where('status', 'published');

        if (! $request->user()?->hasActiveMembership()) {
            $query->where('is_member_only', false);
        }

        $search = $request->query('search');

        if (is_string($search) && $search !== '') {
            $query->where(function ($opportunities) use ($search): void {
                $opportunities->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('organization', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $category = $request->query('category');
        $category = is_string($category) ? $category : '';

        if (is_string($category) && $category !== '') {
            $query->where('category_id', $category);
        }

        $remote = $request->query('remote');
        $remote = is_string($remote) ? $remote : '';

        if (in_array($remote, ['0', '1'], true)) {
            $query->where('is_remote', $remote === '1');
        }

        $bookmarkedOpportunityIds = $request->user()
            ? $request->user()->bookmarks()->where('bookmarkable_type', Opportunity::class)->pluck('bookmarkable_id')->map(fn ($id) => (int) $id)->all()
            : [];

        return view('pages.opportunities', [
            'opportunities' => $query->latest()->paginate(12)->withQueryString(),
            'search' => is_string($search) ? $search : '',
            'category' => $category,
            'remote' => $remote,
            'categories' => OpportunityCategory::all(),
            'bookmarkedOpportunityIds' => $bookmarkedOpportunityIds,
        ]);
    }

    public function chapters(Request $request): View
    {
        $query = Chapter::where('status', 'active');
        $search = $request->query('search');

        if (is_string($search) && $search !== '') {
            $query->where(function ($chapters) use ($search): void {
                $chapters->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return view('pages.chapters', [
            'chapters' => $query->latest()->get(),
            'search' => is_string($search) ? $search : '',
        ]);
    }

    public function news(): View
    {
        $announcements = Announcement::where('is_published', true)
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->take(9)
            ->get();
        $articles = Article::approved()
            ->with('author')
            ->orderByDesc('published_at')
            ->take(6)
            ->get();

        return view('pages.news', compact('announcements', 'articles'));
    }
}
