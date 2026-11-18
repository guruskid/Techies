<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Chapter;
use App\Models\Event;
use App\Models\Hub;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Build an XML sitemap for public pages and published content.
     */
    public function index(): Response
    {
        $staticPages = [
            ['url' => route('home'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['url' => route('about'), 'changefreq' => 'monthly', 'priority' => '0.6'],
            ['url' => route('mission_and_vision'), 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['url' => route('leadership'), 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['url' => route('community'), 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['url' => route('members'), 'changefreq' => 'daily', 'priority' => '0.8'],
            ['url' => route('hubs'), 'changefreq' => 'daily', 'priority' => '0.8'],
            ['url' => route('chapters'), 'changefreq' => 'weekly', 'priority' => '0.6'],
            ['url' => route('events'), 'changefreq' => 'daily', 'priority' => '0.9'],
            ['url' => route('opportunities'), 'changefreq' => 'daily', 'priority' => '0.9'],
            ['url' => route('articles'), 'changefreq' => 'daily', 'priority' => '0.9'],
            ['url' => route('news'), 'changefreq' => 'weekly', 'priority' => '0.5'],
            ['url' => route('contact'), 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['url' => route('community_guidelines'), 'changefreq' => 'yearly', 'priority' => '0.3'],
        ];

        $articles = Article::approved()
            ->whereHas('author', fn (Builder $author) => $author->where('status', 'active'))
            ->orderByDesc('published_at')
            ->limit(500)
            ->get(['slug', 'updated_at', 'published_at'])
            ->map(fn (Article $article) => [
                'url' => route('articles.show', $article),
                'lastmod' => $article->updated_at,
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ]);

        $events = Event::where('status', 'published')
            ->whereDate('start_date', '>=', today())
            ->orderBy('start_date')
            ->limit(500)
            ->get(['slug', 'updated_at'])
            ->map(fn (Event $event) => [
                'url' => route('events.show', $event->slug),
                'lastmod' => $event->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ]);

        $hubs = Hub::where('is_public', true)
            ->where('status', 'approved')
            ->orderBy('name')
            ->limit(500)
            ->get(['slug', 'updated_at'])
            ->map(fn (Hub $hub) => [
                'url' => route('hubs.show', $hub->slug),
                'lastmod' => $hub->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ]);

        $chapters = Chapter::where('status', 'active')
            ->orderBy('name')
            ->get(['slug', 'updated_at'])
            ->map(fn (Chapter $chapter) => [
                'url' => route('chapters.show', $chapter->slug),
                'lastmod' => $chapter->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ]);

        $members = User::where('status', 'active')
            ->whereHas('profile', fn (Builder $profile) => $profile->where('is_public', true)->whereNotNull('username'))
            ->with('profile')
            ->orderBy('name')
            ->limit(1000)
            ->get()
            ->map(fn (User $user) => [
                'url' => $user->profile->publicUrl(),
                'lastmod' => $user->profile->updated_at,
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ]);

        $urls = collect($staticPages)
            ->merge($articles)
            ->merge($events)
            ->merge($hubs)
            ->merge($chapters)
            ->merge($members);

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
