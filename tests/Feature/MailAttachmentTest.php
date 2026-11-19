<?php

namespace Tests\Feature;

use App\Models\EmailAccount;
use App\Models\EmailAttachment;
use App\Models\EmailMessage;
use App\Models\User;
use App\Services\Mail\MimeParser;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MailAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('local');
    }

    public function test_outbound_messages_can_carry_attachments(): void
    {
        Mail::fake();

        $admin = $this->admin();
        $account = $this->account($admin);

        $this->actingAs($admin)
            ->post(route('admin.mail.send'), [
                'email_account_id' => $account->id,
                'to' => 'member@example.org',
                'subject' => 'Your invoice',
                'body' => 'Attached is the invoice.',
                'attachments' => [
                    UploadedFile::fake()->create('invoice.pdf', 120, 'application/pdf'),
                ],
            ])
            ->assertRedirect();

        $message = EmailMessage::firstOrFail();
        $attachment = $message->attachments()->firstOrFail();

        $this->assertSame('invoice.pdf', $attachment->filename);
        $this->assertSame('application/pdf', $attachment->mime_type);
        Storage::disk('local')->assertExists($attachment->path);
    }

    public function test_inbound_webhooks_store_attachments(): void
    {
        $account = $this->account($this->admin());

        $this->postJson(route('mail.inbound', $account->inbound_token), [
            'from' => 'ada@example.org',
            'subject' => 'Notes attached',
            'text' => 'See the attached notes.',
            'attachments' => [
                [
                    'filename' => 'notes.txt',
                    'content_type' => 'text/plain',
                    'content' => base64_encode('Meeting notes'),
                ],
            ],
        ])->assertOk();

        $attachment = EmailAttachment::firstOrFail();

        $this->assertSame('notes.txt', $attachment->filename);
        $this->assertSame('Meeting notes', Storage::disk('local')->get($attachment->path));
    }

    public function test_the_mime_parser_extracts_attachments_from_raw_messages(): void
    {
        $raw = implode("\r\n", [
            'From: Ada Lovelace <ada@example.org>',
            'To: hello@example.org',
            'Subject: Report',
            'Message-Id: <report-1@example.org>',
            'Content-Type: multipart/mixed; boundary="BOUND"',
            '',
            '--BOUND',
            'Content-Type: text/plain; charset="utf-8"',
            '',
            'Please find the report attached.',
            '--BOUND',
            'Content-Type: application/pdf; name="report.pdf"',
            'Content-Disposition: attachment; filename="report.pdf"',
            'Content-Transfer-Encoding: base64',
            '',
            chunk_split(base64_encode('%PDF-1.4 fake'), 76, "\r\n"),
            '--BOUND--',
            '',
        ]);

        $parsed = (new MimeParser)->parse($raw);

        $this->assertStringContainsString('Please find the report attached.', (string) $parsed['body_text']);
        $this->assertCount(1, $parsed['attachments']);
        $this->assertSame('report.pdf', $parsed['attachments'][0]['filename']);
        $this->assertSame('application/pdf', $parsed['attachments'][0]['mime_type']);
        $this->assertStringContainsString('%PDF-1.4 fake', $parsed['attachments'][0]['content']);
    }

    public function test_attachment_downloads_are_scoped_to_mailbox_access(): void
    {
        $owner = $this->admin();
        $outsider = $this->admin();
        $account = $this->account($owner);
        $message = EmailMessage::create([
            'email_account_id' => $account->id,
            'direction' => EmailMessage::DIRECTION_INBOUND,
            'status' => EmailMessage::STATUS_RECEIVED,
            'from_email' => 'ada@example.org',
            'to_email' => $account->email,
            'subject' => 'File',
        ]);
        $attachment = $message->attachments()->create([
            'filename' => 'secret.txt',
            'mime_type' => 'text/plain',
            'size' => 6,
            'disk' => 'local',
            'path' => 'mail/attachments/secret.txt',
        ]);
        Storage::disk('local')->put($attachment->path, 'secret');

        $this->actingAs($owner)
            ->get(route('admin.mail.attachments.download', $attachment))
            ->assertOk();

        $this->actingAs($outsider)
            ->get(route('admin.mail.attachments.download', $attachment))
            ->assertForbidden();
    }

    private function account(User $owner): EmailAccount
    {
        return EmailAccount::create([
            'name' => 'Owner Mailbox',
            'email' => 'owner@example.org',
            'smtp_host' => 'smtp.example.org',
            'smtp_port' => 587,
            'smtp_username' => 'owner@example.org',
            'smtp_password' => 'secret',
            'smtp_encryption' => 'tls',
            'is_active' => true,
            'is_default' => true,
            'created_by' => $owner->id,
        ]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');

        return $admin;
    }
}
