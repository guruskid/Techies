<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admins_can_create_update_and_delete_templates(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.mail.templates.index'))->assertOk()->assertSee('Email templates');
        $this->actingAs($admin)->get(route('admin.mail.templates.create'))->assertOk()->assertSee('New email template');

        $this->actingAs($admin)
            ->post(route('admin.mail.templates.store'), [
                'name' => 'Welcome note',
                'subject' => 'Welcome to {{site_name}}',
                'body' => '<p>Hi {{first_name}}, welcome aboard.</p>',
            ])
            ->assertRedirect(route('admin.mail.templates.index'));

        $template = EmailTemplate::firstOrFail();

        $this->assertSame('Welcome note', $template->name);
        $this->assertSame('Welcome to '.config('app.name'), $template->renderSubject());
        $this->assertSame('<p>Hi Ada, welcome aboard.</p>', $template->renderBody([
            'first_name' => 'Ada',
        ]));

        $this->actingAs($admin)
            ->get(route('admin.mail.templates.edit', $template))
            ->assertOk()
            ->assertSee('Welcome note');

        $this->actingAs($admin)
            ->patch(route('admin.mail.templates.update', $template), [
                'name' => 'Renamed note',
                'subject' => 'Hello again',
                'body' => '<p>Updated body</p>',
            ])
            ->assertRedirect(route('admin.mail.templates.index'));

        $this->assertSame('Renamed note', $template->refresh()->name);

        $this->actingAs($admin)
            ->delete(route('admin.mail.templates.destroy', $template))
            ->assertRedirect();

        $this->assertDatabaseCount('email_templates', 0);
    }

    public function test_members_cannot_manage_templates(): void
    {
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);

        $this->actingAs($member)->get(route('admin.mail.templates.index'))->assertForbidden();
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');

        return $admin;
    }
}
