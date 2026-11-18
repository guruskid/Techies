<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_lists_public_pages_and_published_content_only(): void
    {
        $author = User::factory()->create(['status' => 'active']);

        $article = Article::create([
            'user_id' => $author->id,
            'title' => 'Published Sitemap Article',
            'topic' => 'engineering',
            'body' => str_repeat('A useful article for the sitemap. ', 12),
            'status' => Article::STATUS_APPROVED,
            'published_at' => now(),
        ]);

        $draft = Article::create([
            'user_id' => $author->id,
            'title' => 'Hidden Draft Article',
            'topic' => 'engineering',
            'body' => str_repeat('This draft should never appear. ', 12),
            'status' => Article::STATUS_DRAFT,
        ]);

        $event = Event::create([
            'title' => 'Sitemap Event',
            'slug' => 'sitemap-event',
            'start_date' => now()->addWeek()->toDateString(),
            'status' => 'published',
            'registration_type' => 'free',
        ]);

        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $response->assertSee(route('about'), false);
        $response->assertSee(route('articles.show', $article), false);
        $response->assertSee(route('events.show', $event->slug), false);
        $response->assertDontSee($draft->slug);
    }

    public function test_robots_txt_points_to_the_sitemap(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertSee('Sitemap: '.route('sitemap'))
            ->assertSee('Disallow: /admin');
    }

    public function test_articles_feed_contains_only_approved_articles(): void
    {
        $author = User::factory()->create(['status' => 'active']);

        Article::create([
            'user_id' => $author->id,
            'title' => 'Feed Article',
            'topic' => 'engineering',
            'body' => str_repeat('Feed body content. ', 12),
            'status' => Article::STATUS_APPROVED,
            'published_at' => now(),
        ]);

        Article::create([
            'user_id' => $author->id,
            'title' => 'Draft Feed Article',
            'topic' => 'engineering',
            'body' => str_repeat('Draft feed body content. ', 12),
            'status' => Article::STATUS_DRAFT,
        ]);

        $response = $this->get(route('feed.articles'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/rss+xml; charset=utf-8');
        $response->assertSee('<title>Feed Article</title>', false);
        $response->assertDontSee('Draft Feed Article');
        $response->assertSee('<rss version="2.0"', false);
    }

    public function test_events_feed_lists_upcoming_published_events(): void
    {
        Event::create([
            'title' => 'Upcoming Feed Event',
            'slug' => 'upcoming-feed-event',
            'start_date' => now()->addWeek()->toDateString(),
            'status' => 'published',
            'registration_type' => 'free',
        ]);

        Event::create([
            'title' => 'Draft Feed Event',
            'slug' => 'draft-feed-event',
            'start_date' => now()->addWeek()->toDateString(),
            'status' => 'draft',
            'registration_type' => 'free',
        ]);

        $response = $this->get(route('feed.events'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/rss+xml; charset=utf-8');
        $response->assertSee('Upcoming Feed Event');
        $response->assertDontSee('Draft Feed Event');
    }
}
