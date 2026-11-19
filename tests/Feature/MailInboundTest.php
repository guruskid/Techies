<?php

namespace Tests\Feature;

use App\Models\EmailAccount;
use App\Models\EmailMessage;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailInboundTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_a_mailgun_webhook_stores_an_inbound_message(): void
    {
        $account = $this->account();

        $this->postJson(route('mail.inbound', $account->inbound_token), [
            'sender' => 'ada@example.org',
            'From' => 'Ada Lovelace <ada@example.org>',
            'recipient' => $account->email,
            'subject' => 'Re: Chapter meetup',
            'body-plain' => 'Thanks, I will be there.',
            'body-html' => '<p>Thanks, I will be there.</p>',
            'Message-Id' => '<reply-1@example.org>',
        ])->assertOk()->assertJson(['status' => 'ok', 'stored' => true]);

        $message = EmailMessage::firstOrFail();

        $this->assertSame(EmailMessage::DIRECTION_INBOUND, $message->direction);
        $this->assertSame(EmailMessage::STATUS_RECEIVED, $message->status);
        $this->assertSame('ada@example.org', $message->from_email);
        $this->assertSame('Ada Lovelace', $message->from_name);
        $this->assertSame('Re: Chapter meetup', $message->subject);
        $this->assertSame('reply-1@example.org', $message->message_id);
        $this->assertNull($message->read_at);
    }

    public function test_a_postmark_webhook_is_supported(): void
    {
        $account = $this->account();

        $this->postJson(route('mail.inbound', $account->inbound_token), [
            'FromFull' => ['Email' => 'grace@example.org', 'Name' => 'Grace Hopper'],
            'To' => $account->email,
            'Subject' => 'Hello from Postmark',
            'TextBody' => 'A plain text message.',
            'HtmlBody' => '<p>A plain text message.</p>',
            'MessageID' => 'postmark-123',
        ])->assertOk();

        $message = EmailMessage::firstOrFail();

        $this->assertSame('grace@example.org', $message->from_email);
        $this->assertSame('Grace Hopper', $message->from_name);
        $this->assertSame('Hello from Postmark', $message->subject);
    }

    public function test_duplicate_message_ids_are_ignored(): void
    {
        $account = $this->account();
        $payload = [
            'from' => 'ada@example.org',
            'subject' => 'Only once',
            'text' => 'Body',
            'message_id' => 'duplicate-123@example.org',
        ];

        $this->postJson(route('mail.inbound', $account->inbound_token), $payload)->assertOk();
        $this->postJson(route('mail.inbound', $account->inbound_token), $payload)->assertOk();

        $this->assertSame(1, EmailMessage::count());
    }

    public function test_an_unknown_token_is_rejected(): void
    {
        $this->postJson(route('mail.inbound', 'wrong-token'), [
            'from' => 'ada@example.org',
            'subject' => 'Hello',
        ])->assertNotFound();

        $this->assertDatabaseCount('email_messages', 0);
    }

    public function test_inactive_mailboxes_do_not_receive_webhooks(): void
    {
        $account = $this->account(['is_active' => false]);

        $this->postJson(route('mail.inbound', $account->inbound_token), [
            'from' => 'ada@example.org',
            'subject' => 'Hello',
        ])->assertNotFound();
    }

    public function test_a_valid_sender_is_required(): void
    {
        $account = $this->account();

        $this->postJson(route('mail.inbound', $account->inbound_token), [
            'subject' => 'No sender',
        ])->assertStatus(422);

        $this->assertDatabaseCount('email_messages', 0);
    }

    public function test_mailboxes_that_require_a_signature_reject_unsigned_webhooks(): void
    {
        $account = $this->account(['requires_signature' => true]);

        $this->postJson(route('mail.inbound', $account->inbound_token), [
            'from' => 'ada@example.org',
            'subject' => 'Unsigned',
        ])->assertForbidden();

        $this->assertDatabaseCount('email_messages', 0);
    }

    public function test_a_valid_hmac_signature_is_accepted(): void
    {
        $account = $this->account(['requires_signature' => true]);
        $payload = json_encode([
            'from' => 'ada@example.org',
            'subject' => 'Signed message',
            'text' => 'Trusted body',
        ]);

        $signature = 'sha256='.hash_hmac('sha256', $payload, $account->inbound_secret);

        $this->call(
            'POST',
            route('mail.inbound', $account->inbound_token),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_TECHIES_SIGNATURE' => $signature,
            ],
            $payload,
        )->assertOk()->assertJson(['status' => 'ok', 'stored' => true]);

        $this->assertSame(1, EmailMessage::count());
    }

    public function test_an_invalid_hmac_signature_is_rejected(): void
    {
        $account = $this->account(['requires_signature' => true]);
        $payload = json_encode(['from' => 'ada@example.org', 'subject' => 'Tampered']);

        $this->call(
            'POST',
            route('mail.inbound', $account->inbound_token),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_TECHIES_SIGNATURE' => 'sha256=not-the-right-signature',
            ],
            $payload,
        )->assertForbidden();

        $this->assertDatabaseCount('email_messages', 0);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function account(array $overrides = []): EmailAccount
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        return EmailAccount::create(array_merge([
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
        ], $overrides));
    }
}
