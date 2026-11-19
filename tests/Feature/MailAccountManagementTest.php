<?php

namespace Tests\Feature;

use App\Models\EmailAccount;
use App\Models\User;
use App\Services\Mail\MailboxSynchronizer;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MailAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admins_can_open_the_mail_center(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.mail.index'))
            ->assertOk()
            ->assertSee('Mail center');
    }

    public function test_admins_can_connect_a_mailbox_and_the_password_is_encrypted(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.mail.accounts.store'), [
                'name' => 'Community team',
                'email' => 'hello@example.org',
                'from_name' => 'MWTT Community',
                'smtp_host' => 'smtp.example.org',
                'smtp_port' => 587,
                'smtp_username' => 'hello@example.org',
                'smtp_password' => 'super-secret-password',
                'smtp_encryption' => 'tls',
                'imap_host' => 'imap.example.org',
                'imap_port' => 993,
                'imap_username' => 'hello@example.org',
                'imap_password' => 'imap-secret',
                'imap_encryption' => 'ssl',
                'is_active' => '1',
                'is_default' => '1',
            ])
            ->assertRedirect();

        $account = EmailAccount::firstOrFail();

        $this->assertSame('hello@example.org', $account->email);
        $this->assertTrue($account->is_default);
        $this->assertSame('super-secret-password', $account->smtp_password);
        $this->assertSame('imap-secret', $account->imap_password);
        $this->assertNotSame('super-secret-password', DB::table('email_accounts')->value('smtp_password'));
        $this->assertNotSame('imap-secret', DB::table('email_accounts')->value('imap_password'));
        $this->assertNotNull($account->inbound_token);
    }

    public function test_only_one_mailbox_is_the_default_sender(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.mail.accounts.store'), $this->payload([
            'name' => 'First',
            'email' => 'first@example.org',
            'is_default' => '1',
        ]));

        $this->actingAs($admin)->post(route('admin.mail.accounts.store'), $this->payload([
            'name' => 'Second',
            'email' => 'second@example.org',
            'is_default' => '1',
        ]));

        $this->assertSame(1, EmailAccount::where('is_default', true)->count());
        $this->assertTrue(EmailAccount::where('email', 'second@example.org')->firstOrFail()->is_default);
    }

    public function test_editing_without_a_password_keeps_the_saved_one(): void
    {
        $admin = $this->admin();
        $account = EmailAccount::create($this->payload([
            'name' => 'Community team',
            'email' => 'hello@example.org',
            'smtp_password' => 'original-secret',
            'created_by' => $admin->id,
        ]));

        $this->actingAs($admin)->patch(route('admin.mail.accounts.update', $account), $this->payload([
            'name' => 'Renamed team',
            'email' => 'hello@example.org',
            'smtp_password' => '',
            'imap_password' => '',
        ]))->assertRedirect();

        $account->refresh();

        $this->assertSame('Renamed team', $account->name);
        $this->assertSame('original-secret', $account->smtp_password);
    }

    public function test_members_cannot_manage_mailboxes(): void
    {
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);

        $this->actingAs($member)->get(route('admin.mail.index'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.mail.accounts.index'))->assertForbidden();
    }

    public function test_rotating_inbound_credentials_replaces_the_token_and_secret(): void
    {
        $admin = $this->admin();
        $account = EmailAccount::create($this->payload([
            'name' => 'Community team',
            'email' => 'hello@example.org',
            'created_by' => $admin->id,
        ]));

        $oldToken = $account->inbound_token;
        $oldSecret = $account->inbound_secret;

        $this->actingAs($admin)
            ->post(route('admin.mail.accounts.rotate-inbound', $account))
            ->assertRedirect()
            ->assertSessionHas('success');

        $account->refresh();

        $this->assertNotSame($oldToken, $account->inbound_token);
        $this->assertNotSame($oldSecret, $account->inbound_secret);
        $this->assertNotNull($account->inbound_token);
        $this->assertNotNull($account->inbound_secret);
    }

    public function test_imap_connection_test_reports_success_or_failure(): void
    {
        $admin = $this->admin();
        $account = EmailAccount::create($this->payload([
            'name' => 'Community team',
            'email' => 'hello@example.org',
            'imap_host' => 'imap.example.org',
            'imap_username' => 'hello@example.org',
            'imap_password' => 'imap-secret',
            'created_by' => $admin->id,
        ]));

        $this->mock(MailboxSynchronizer::class, function ($mock): void {
            $mock->shouldReceive('testConnection')->once();
        });

        $this->actingAs($admin)
            ->post(route('admin.mail.accounts.test-imap', $account))
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Community team',
            'email' => 'hello@example.org',
            'from_name' => 'MWTT Community',
            'smtp_host' => 'smtp.example.org',
            'smtp_port' => 587,
            'smtp_username' => 'hello@example.org',
            'smtp_password' => 'super-secret-password',
            'smtp_encryption' => 'tls',
            'imap_encryption' => 'ssl',
            'is_active' => '1',
        ], $overrides);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');

        return $admin;
    }
}
