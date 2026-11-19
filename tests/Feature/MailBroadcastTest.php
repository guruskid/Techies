<?php

namespace Tests\Feature;

use App\Jobs\SendScheduledBroadcast;
use App\Models\Chapter;
use App\Models\EmailAccount;
use App\Models\EmailBroadcast;
use App\Models\EmailBroadcastRecipient;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Hub;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Services\Mail\BroadcastAudience;
use App\Services\Mail\BroadcastSender;
use App\Services\Mail\MailAccountMailer;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class MailBroadcastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_storing_a_broadcast_snapshots_the_audience(): void
    {
        $admin = $this->admin();
        $account = $this->account($admin);

        $activeOne = User::factory()->create(['status' => 'active']);
        $activeTwo = User::factory()->create(['status' => 'active']);
        $suspended = User::factory()->create(['status' => 'suspended']);

        $this->actingAs($admin)
            ->post(route('admin.mail.broadcasts.store'), [
                'name' => 'Community update',
                'email_account_id' => $account->id,
                'subject' => 'Hello {{first_name}}',
                'body' => '<p>News for {{name}}</p>',
                'audience' => 'all_members',
            ])
            ->assertRedirect();

        $broadcast = EmailBroadcast::firstOrFail();

        $this->assertSame(EmailBroadcast::STATUS_DRAFT, $broadcast->status);
        $this->assertSame(User::where('status', 'active')->count(), $broadcast->recipients_count);
        $this->assertTrue($broadcast->recipients()->where('email', $activeOne->email)->exists());
        $this->assertTrue($broadcast->recipients()->where('email', $activeTwo->email)->exists());
        $this->assertFalse($broadcast->recipients()->where('email', $suspended->email)->exists());
    }

    public function test_chapter_audiences_only_include_chapter_members(): void
    {
        $chapter = Chapter::create(['name' => 'Lagos Builders', 'slug' => 'lagos-builders', 'status' => 'active']);
        $inside = User::factory()->create(['status' => 'active']);
        User::factory()->create(['status' => 'active']);
        $chapter->members()->attach($inside->id);

        $recipients = (new BroadcastAudience)->recipients('chapter', $chapter->id);

        $this->assertCount(1, $recipients);
        $this->assertSame($inside->email, $recipients->first()['email']);
    }

    public function test_event_audiences_only_include_confirmed_attendees(): void
    {
        $event = Event::create([
            'title' => 'Meetup',
            'slug' => 'meetup',
            'start_date' => today()->addWeek()->toDateString(),
            'status' => 'published',
            'registration_type' => 'free',
        ]);
        $confirmed = User::factory()->create(['status' => 'active']);
        $waitlisted = User::factory()->create(['status' => 'active']);

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $confirmed->id,
            'status' => EventRegistration::STATUS_REGISTERED,
        ]);
        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $waitlisted->id,
            'status' => EventRegistration::STATUS_WAITLISTED,
        ]);

        $recipients = (new BroadcastAudience)->recipients('event', $event->id);

        $this->assertSame([$confirmed->email], $recipients->pluck('email')->all());
    }

    public function test_newsletter_and_hub_owner_audiences(): void
    {
        NewsletterSubscriber::create(['name' => 'Reader', 'email' => 'reader@example.org']);

        $owner = User::factory()->create(['status' => 'active']);
        Hub::create([
            'user_id' => $owner->id,
            'name' => 'Owner Hub',
            'slug' => 'owner-hub',
            'status' => 'approved',
        ]);

        $newsletter = (new BroadcastAudience)->recipients('newsletter');
        $owners = (new BroadcastAudience)->recipients('hub_owners');

        $this->assertSame(['reader@example.org'], $newsletter->pluck('email')->all());
        $this->assertSame([$owner->email], $owners->pluck('email')->all());
    }

    public function test_sending_a_broadcast_personalises_merge_tags_and_updates_counts(): void
    {
        $admin = $this->admin();
        $account = $this->account($admin);
        $member = User::factory()->create(['status' => 'active', 'name' => 'Ada Lovelace', 'email' => 'ada@example.org']);

        $broadcast = EmailBroadcast::create([
            'email_account_id' => $account->id,
            'created_by' => $admin->id,
            'name' => 'Community update',
            'subject' => 'Hello {{first_name}}',
            'body' => '<p>News for {{name}} ({{email}}).</p>',
            'audience' => 'all_members',
            'status' => EmailBroadcast::STATUS_DRAFT,
            'recipients_count' => 1,
        ]);
        EmailBroadcastRecipient::create([
            'email_broadcast_id' => $broadcast->id,
            'user_id' => $member->id,
            'email' => $member->email,
            'name' => $member->name,
        ]);

        $delivered = [];
        $mailer = Mockery::mock(MailAccountMailer::class);
        $mailer->shouldReceive('deliver')
            ->once()
            ->andReturnUsing(function ($account, $message) use (&$delivered) {
                $delivered[] = $message;

                return 'test-message-id';
            });

        (new BroadcastSender($mailer))->send($broadcast);

        $broadcast->refresh();

        $this->assertSame(EmailBroadcast::STATUS_SENT, $broadcast->status);
        $this->assertSame(1, $broadcast->sent_count);
        $this->assertSame(0, $broadcast->failed_count);
        $this->assertNotNull($broadcast->sent_at);
        $this->assertSame('sent', $broadcast->recipients()->first()->status);

        $this->assertCount(1, $delivered);
        $this->assertSame('Hello Ada', $delivered[0]->subject);
        $this->assertStringContainsString('News for Ada Lovelace (ada@example.org)', $delivered[0]->body_html);
    }

    public function test_the_broadcast_detail_page_shows_delivery_progress(): void
    {
        $admin = $this->admin();
        $account = $this->account($admin);
        $member = User::factory()->create(['status' => 'active', 'name' => 'Ada Lovelace', 'email' => 'ada@example.org']);

        $broadcast = EmailBroadcast::create([
            'email_account_id' => $account->id,
            'created_by' => $admin->id,
            'name' => 'Community update',
            'subject' => 'Hello {{first_name}}',
            'body' => '<p>News for {{name}}</p>',
            'audience' => 'all_members',
            'status' => EmailBroadcast::STATUS_DRAFT,
            'recipients_count' => 1,
        ]);

        EmailBroadcastRecipient::create([
            'email_broadcast_id' => $broadcast->id,
            'user_id' => $member->id,
            'email' => $member->email,
            'name' => $member->name,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.mail.broadcasts.show', $broadcast))
            ->assertOk()
            ->assertSee('Community update')
            ->assertSee('Send broadcast')
            ->assertSee('ada@example.org');

        $this->actingAs($admin)
            ->get(route('admin.mail.broadcasts.index'))
            ->assertOk()
            ->assertSee('Community update');
    }

    public function test_members_cannot_create_broadcasts(): void
    {
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);

        $this->actingAs($member)->get(route('admin.mail.broadcasts.create'))->assertForbidden();
    }

    public function test_a_broadcast_can_be_scheduled_for_a_future_time(): void
    {
        Queue::fake();

        $admin = $this->admin();
        $account = $this->account($admin);
        User::factory()->create(['status' => 'active']);
        $scheduledAt = now()->addHour();

        $this->actingAs($admin)
            ->post(route('admin.mail.broadcasts.store'), [
                'name' => 'Scheduled update',
                'email_account_id' => $account->id,
                'subject' => 'Later',
                'body' => '<p>Sent later.</p>',
                'audience' => 'all_members',
                'scheduled_at' => $scheduledAt->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $broadcast = EmailBroadcast::firstOrFail();

        $this->assertSame(EmailBroadcast::STATUS_SCHEDULED, $broadcast->status);
        $this->assertNotNull($broadcast->scheduled_at);
        Queue::assertPushed(SendScheduledBroadcast::class, fn (SendScheduledBroadcast $job) => $job->broadcastId === $broadcast->id);
    }

    public function test_an_invalid_past_schedule_is_rejected(): void
    {
        $admin = $this->admin();
        $account = $this->account($admin);

        $this->actingAs($admin)
            ->post(route('admin.mail.broadcasts.store'), [
                'name' => 'Past update',
                'email_account_id' => $account->id,
                'subject' => 'Too late',
                'body' => '<p>Too late.</p>',
                'audience' => 'all_members',
                'scheduled_at' => now()->subHour()->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('scheduled_at');
    }

    public function test_the_due_command_sends_scheduled_broadcasts(): void
    {
        Mail::fake();

        $admin = $this->admin();
        $account = $this->account($admin);
        $member = User::factory()->create(['status' => 'active', 'name' => 'Ada', 'email' => 'ada@example.org']);

        $broadcast = EmailBroadcast::create([
            'email_account_id' => $account->id,
            'created_by' => $admin->id,
            'name' => 'Due update',
            'subject' => 'Now',
            'body' => '<p>Now.</p>',
            'audience' => 'all_members',
            'status' => EmailBroadcast::STATUS_SCHEDULED,
            'scheduled_at' => now()->subMinute(),
            'recipients_count' => 1,
        ]);
        EmailBroadcastRecipient::create([
            'email_broadcast_id' => $broadcast->id,
            'user_id' => $member->id,
            'email' => $member->email,
            'name' => $member->name,
        ]);

        $this->artisan('mail:broadcasts:send-due')->assertSuccessful();

        $this->assertSame(EmailBroadcast::STATUS_SENT, $broadcast->refresh()->status);
        $this->assertSame(1, $broadcast->sent_count);
    }

    public function test_the_artisan_command_sends_a_prepared_broadcast(): void
    {
        Mail::fake();

        $admin = $this->admin();
        $account = $this->account($admin);
        $member = User::factory()->create(['status' => 'active', 'name' => 'Grace Hopper', 'email' => 'grace@example.org']);

        $broadcast = EmailBroadcast::create([
            'email_account_id' => $account->id,
            'created_by' => $admin->id,
            'name' => 'CLI broadcast',
            'subject' => 'Hello {{first_name}}',
            'body' => '<p>Hi {{first_name}}</p>',
            'audience' => 'all_members',
            'status' => EmailBroadcast::STATUS_DRAFT,
            'recipients_count' => 1,
        ]);

        EmailBroadcastRecipient::create([
            'email_broadcast_id' => $broadcast->id,
            'user_id' => $member->id,
            'email' => $member->email,
            'name' => $member->name,
        ]);

        $this->artisan('mail:broadcast', ['broadcast' => $broadcast->id])->assertSuccessful();

        $this->assertSame(EmailBroadcast::STATUS_SENT, $broadcast->refresh()->status);
        $this->assertSame(1, $broadcast->sent_count);
        $this->assertSame('sent', $broadcast->recipients()->first()->status);
    }

    public function test_the_artisan_command_rejects_an_unknown_broadcast(): void
    {
        $this->artisan('mail:broadcast', ['broadcast' => 999])->assertFailed();
    }

    private function account(User $admin): EmailAccount
    {
        return EmailAccount::create([
            'name' => 'Community team',
            'email' => 'hello@example.org',
            'smtp_host' => 'smtp.example.org',
            'smtp_port' => 587,
            'smtp_username' => 'hello@example.org',
            'smtp_password' => 'secret',
            'smtp_encryption' => 'tls',
            'is_active' => true,
            'is_default' => true,
            'created_by' => $admin->id,
        ]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');

        return $admin;
    }
}
