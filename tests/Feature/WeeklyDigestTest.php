<?php

namespace Tests\Feature;

use App\Mail\WeeklyDigest;
use App\Models\Article;
use App\Models\Event;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WeeklyDigestTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_digest_is_only_sent_to_members_who_opted_in(): void
    {
        Mail::fake();

        $optedIn = $this->member();
        $optedIn->ensureProfile()->update(['weekly_digest' => true]);

        $optedOut = $this->member();
        $optedOut->ensureProfile()->update(['weekly_digest' => false]);

        $this->seedContent();

        $this->artisan('community:send-digest')->assertSuccessful();

        Mail::assertSent(WeeklyDigest::class, fn (WeeklyDigest $mail) => $mail->hasTo($optedIn->email));
        Mail::assertNotSent(WeeklyDigest::class, fn (WeeklyDigest $mail) => $mail->hasTo($optedOut->email));
        Mail::assertSent(WeeklyDigest::class, 1);
    }

    public function test_no_digest_is_sent_when_there_is_nothing_to_share(): void
    {
        Mail::fake();

        $member = $this->member();
        $member->ensureProfile()->update(['weekly_digest' => true]);

        $this->artisan('community:send-digest')
            ->expectsOutputToContain('Nothing to include this week')
            ->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_the_digest_includes_upcoming_events_articles_and_opportunities(): void
    {
        Mail::fake();

        $member = $this->member();
        $member->ensureProfile()->update(['weekly_digest' => true]);

        [$event, $article, $opportunity] = $this->seedContent();

        $this->artisan('community:send-digest')->assertSuccessful();

        Mail::assertSent(WeeklyDigest::class, function (WeeklyDigest $mail) use ($event, $article, $opportunity): bool {
            $html = $mail->render();

            return str_contains($html, $event->title)
                && str_contains($html, $article->title)
                && str_contains($html, $opportunity->title);
        });
    }

    public function test_a_dry_run_reports_recipients_without_sending_email(): void
    {
        Mail::fake();

        $member = $this->member();
        $member->ensureProfile()->update(['weekly_digest' => true]);

        $this->seedContent();

        $this->artisan('community:send-digest', ['--dry-run' => true])
            ->expectsOutputToContain('would receive')
            ->assertSuccessful();

        Mail::assertNothingSent();
    }

    /**
     * @return array{0: Event, 1: Article, 2: Opportunity}
     */
    private function seedContent(): array
    {
        $event = Event::create([
            'title' => 'Weekly Digest Meetup',
            'slug' => 'weekly-digest-meetup',
            'start_date' => today()->addDays(3)->toDateString(),
            'status' => 'published',
            'registration_type' => 'free',
        ]);

        $article = Article::create([
            'user_id' => $this->member()->id,
            'title' => 'Weekly Digest Article',
            'topic' => 'engineering',
            'body' => str_repeat('Useful content for the weekly digest. ', 10),
            'status' => Article::STATUS_APPROVED,
            'published_at' => now(),
        ]);

        $opportunity = Opportunity::create([
            'title' => 'Weekly Digest Role',
            'slug' => 'weekly-digest-role',
            'organization' => 'MWTT Studio',
            'status' => 'published',
        ]);

        return [$event, $article, $opportunity];
    }

    private function member(): User
    {
        return User::factory()->create(['role' => 'member', 'status' => 'active']);
    }
}
