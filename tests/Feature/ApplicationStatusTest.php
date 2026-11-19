<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Models\OpportunityApplication;
use App\Models\User;
use App\Notifications\ApplicationStatusUpdated;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApplicationStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_update_an_application_status_and_the_applicant_is_emailed(): void
    {
        Notification::fake();

        $admin = $this->admin();
        $applicant = User::factory()->create(['role' => 'member', 'status' => 'active']);
        $opportunity = $this->opportunity();
        $application = OpportunityApplication::create([
            'opportunity_id' => $opportunity->id,
            'user_id' => $applicant->id,
            'status' => 'applied',
            'applied_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.opportunities.applications.update', [$opportunity, $application]), [
                'status' => 'shortlisted',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('shortlisted', $application->fresh()->status);
        Notification::assertSentTo($applicant, ApplicationStatusUpdated::class);
    }

    public function test_members_cannot_update_application_statuses(): void
    {
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);
        $applicant = User::factory()->create(['role' => 'member', 'status' => 'active']);
        $opportunity = $this->opportunity();
        $application = OpportunityApplication::create([
            'opportunity_id' => $opportunity->id,
            'user_id' => $applicant->id,
            'status' => 'applied',
            'applied_at' => now(),
        ]);

        $this->actingAs($member)
            ->post(route('admin.opportunities.applications.update', [$opportunity, $application]), [
                'status' => 'accepted',
            ])
            ->assertForbidden();

        $this->assertSame('applied', $application->fresh()->status);
    }

    public function test_application_must_belong_to_the_opportunity(): void
    {
        $admin = $this->admin();
        $applicant = User::factory()->create(['role' => 'member', 'status' => 'active']);
        $opportunity = $this->opportunity();
        $otherOpportunity = $this->opportunity(['title' => 'Another Role', 'slug' => 'another-role']);
        $application = OpportunityApplication::create([
            'opportunity_id' => $otherOpportunity->id,
            'user_id' => $applicant->id,
            'status' => 'applied',
            'applied_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.opportunities.applications.update', [$opportunity, $application]), [
                'status' => 'rejected',
            ])
            ->assertNotFound();
    }

    public function test_admins_can_download_the_applicant_csv(): void
    {
        $admin = $this->admin();
        $applicant = User::factory()->create([
            'role' => 'member',
            'status' => 'active',
            'name' => 'Ada Applicant',
            'email' => 'ada@example.com',
        ]);
        $opportunity = $this->opportunity();
        OpportunityApplication::create([
            'opportunity_id' => $opportunity->id,
            'user_id' => $applicant->id,
            'status' => 'shortlisted',
            'applied_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.opportunities.applications.export', $opportunity));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('opportunity-'.$opportunity->slug.'-applicants.csv', (string) $response->headers->get('content-disposition'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Name,Email,Phone,Status', $csv);
        $this->assertStringContainsString('Ada Applicant', $csv);
        $this->assertStringContainsString('ada@example.com', $csv);
        $this->assertStringContainsString('shortlisted', $csv);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');

        return $admin;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function opportunity(array $overrides = []): Opportunity
    {
        return Opportunity::create(array_merge([
            'title' => 'Junior Laravel Developer',
            'slug' => 'junior-laravel-developer',
            'organization' => 'Techies Studio',
            'status' => 'published',
        ], $overrides));
    }
}
