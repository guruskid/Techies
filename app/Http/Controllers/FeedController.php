<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    /**
     * RSS feed for the latest approved articles.
     */
    public function articles(): Response
    {
        $articles = Article::approved()
            ->with('author')
            ->whereHas('author', fn (Builder $author) => $author->where('status', 'active'))
            ->orderByDesc('published_at')
            ->limit(30)
            ->get();

        return response()
            ->view('feeds.articles', [
                'articles' => $articles,
                'updatedAt' => $articles->first()?->published_at ?? now(),
            ])
            ->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }

    /**
     * RSS feed for upcoming published events.
     */
    public function events(): Response
    {
        $events = Event::where('status', 'published')
            ->whereDate('start_date', '>=', today())
            ->orderBy('start_date')
            ->limit(30)
            ->get();

        return response()
            ->view('feeds.events', [
                'events' => $events,
                'updatedAt' => $events->first()?->updated_at ?? now(),
            ])
            ->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }
}
