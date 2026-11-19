<?php

namespace Tests\Feature;

use App\Models\EmailAccount;
use App\Models\EmailDraft;
use App\Models\EmailMessage;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailComposeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admins_can_send_a_message_from_a_connected_mailbox(): void
    {
        Mail::fake();

        $admin = $this->admin();
        $account = $this->account($admin);

        $this->actingAs($admin)
            ->post(route('admin.mail.send'), [
                'email_account_id' => $account->id,
                'to' => 'member@example.org',
                'cc' => 'observer@example.org',
                'subject' => 'Welcome to the community',
                'body' => 'Hello there, welcome aboard.',
            ])
            ->assertRedirect();

        $message = EmailMessage::firstOrFail();

        $this->assertSame(EmailMessage::DIRECTION_OUTBOUND, $message->direction);
        $this->assertSame(EmailMessage::STATUS_SENT, $message->status);
        $this->assertSame('member@example.org', $message->to_email);
        $this->assertSame('observer@example.org', $message->cc);
        $this->assertNotNull($message->sent_at);
    }

    public function test_invalid_recipients_are_rejected(): void
    {
        Mail::fake();

        $admin = $this->admin();
        $account = $this->account($admin);

        $this->actingAs($admin)
            ->post(route('admin.mail.send'), [
                'email_account_id' => $account->id,
                'to' => 'not-an-email',
                'subject' => 'Hello',
                'body' => 'Body',
            ])
            ->assertSessionHasErrors('to');

        $this->assertDatabaseCount('email_messages', 0);
    }

    public function test_replies_prefill_the_recipient_and_subject(): void
    {
        $admin = $this->admin();
        $account = $this->account($admin);
        $received = EmailMessage::create([
            'email_account_id' => $account->id,
            'direction' => EmailMessage::DIRECTION_INBOUND,
            'status' => EmailMessage::STATUS_RECEIVED,
            'from_email' => 'ada@example.org',
            'from_name' => 'Ada Lovelace',
            'to_email' => $account->email,
            'subject' => 'Question about the chapter',
            'body_text' => 'Hello!',
            'received_at' => now(),
            'message_id' => 'incoming-123@example.org',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.mail.compose', ['reply' => $received->id]))
            ->assertOk()
            ->assertSee('Re: Question about the chapter')
            ->assertSee('ada@example.org');
    }

    public function test_members_cannot_send_mail(): void
    {
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);

        $this->actingAs($member)->get(route('admin.mail.compose'))->assertForbidden();
    }

    public function test_compose_autosave_stores_and_restores_a_draft(): void
    {
        $admin = $this->admin();
        $account = $this->account($admin);

        $this->actingAs($admin)
            ->postJson(route('admin.mail.drafts.save'), [
                'email_account_id' => $account->id,
                'to' => 'ada@example.org',
                'subject' => 'Unfinished message',
                'body' => '<p>Half-written.</p>',
            ])
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $draft = EmailDraft::firstOrFail();

        $this->assertSame($admin->id, $draft->user_id);
        $this->assertSame('Unfinished message', $draft->subject);

        $this->actingAs($admin)
            ->get(route('admin.mail.compose'))
            ->assertOk()
            ->assertSee('Unfinished message')
            ->assertSee('Half-written.');
    }

    public function test_sending_a_message_clears_the_draft(): void
    {
        Mail::fake();

        $admin = $this->admin();
        $account = $this->account($admin);

        EmailDraft::create([
            'user_id' => $admin->id,
            'to' => 'ada@example.org',
            'subject' => 'Draft subject',
            'body' => 'Draft body',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.mail.send'), [
                'email_account_id' => $account->id,
                'to' => 'ada@example.org',
                'subject' => 'Final subject',
                'body' => 'Final body',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('email_drafts', 0);
    }

    public function test_admins_can_discard_their_draft(): void
    {
        $admin = $this->admin();

        EmailDraft::create([
            'user_id' => $admin->id,
            'to' => 'ada@example.org',
            'subject' => 'Discard me',
            'body' => 'Discard me',
        ]);

        $this->actingAs($admin)
            ->deleteJson(route('admin.mail.drafts.discard'))
            ->assertOk();

        $this->assertDatabaseCount('email_drafts', 0);
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
