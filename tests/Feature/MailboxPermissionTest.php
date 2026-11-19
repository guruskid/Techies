<?php

namespace Tests\Feature;

use App\Models\EmailAccount;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailboxPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_mailboxes_are_private_to_their_creator(): void
    {
        $owner = $this->admin();
        $outsider = $this->admin();
        $account = $this->account($owner);

        $this->actingAs($owner)->get(route('admin.mail.accounts.index'))->assertSee($account->name);
        $this->actingAs($outsider)->get(route('admin.mail.accounts.index'))->assertDontSee($account->name);

        $this->actingAs($outsider)->get(route('admin.mail.accounts.edit', $account))->assertForbidden();
    }

    public function test_a_shared_viewer_can_read_the_mailbox_but_not_change_it(): void
    {
        $owner = $this->admin();
        $viewer = $this->admin();
        $account = $this->account($owner);
        $account->sharedUsers()->attach($viewer->id, ['can_manage' => false]);

        $this->actingAs($viewer)->get(route('admin.mail.accounts.index'))->assertSee($account->name);
        $this->actingAs($viewer)->get(route('admin.mail.accounts.edit', $account))->assertForbidden();
        $this->actingAs($viewer)->post(route('admin.mail.accounts.rotate-inbound', $account))->assertForbidden();
    }

    public function test_a_shared_manager_can_change_the_mailbox(): void
    {
        $owner = $this->admin();
        $manager = $this->admin();
        $account = $this->account($owner);
        $account->sharedUsers()->attach($manager->id, ['can_manage' => true]);

        $this->actingAs($manager)->get(route('admin.mail.accounts.edit', $account))->assertOk();
        $this->actingAs($manager)->post(route('admin.mail.accounts.rotate-inbound', $account))->assertRedirect();
    }

    public function test_an_outsider_cannot_send_from_or_broadcast_with_a_private_mailbox(): void
    {
        $owner = $this->admin();
        $outsider = $this->admin();
        $account = $this->account($owner);

        $this->actingAs($outsider)
            ->post(route('admin.mail.send'), [
                'email_account_id' => $account->id,
                'to' => 'member@example.org',
                'subject' => 'Blocked',
                'body' => 'You should not read this.',
            ])
            ->assertForbidden();

        $this->actingAs($outsider)
            ->post(route('admin.mail.broadcasts.store'), [
                'name' => 'Blocked broadcast',
                'email_account_id' => $account->id,
                'subject' => 'Blocked',
                'body' => 'Blocked',
                'audience' => 'all_members',
            ])
            ->assertForbidden();
    }

    public function test_super_admins_can_see_every_mailbox(): void
    {
        $owner = $this->admin();
        $account = $this->account($owner);

        $superAdmin = User::factory()->create(['role' => 'super_admin', 'status' => 'active']);
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin)
            ->get(route('admin.mail.accounts.index'))
            ->assertOk()
            ->assertSee($account->name);

        $this->actingAs($superAdmin)
            ->get(route('admin.mail.accounts.edit', $account))
            ->assertOk();
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
