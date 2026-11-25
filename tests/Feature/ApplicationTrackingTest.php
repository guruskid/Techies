<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Models\OpportunityApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_only_see_their_own_applications(): void
    {
        $member = User::factory()->create(['email_verified_at' => now()]);
        $otherMember = User::factory()->create();
        $opportunity = Opportunity::create([
            'title' => 'Product Designer',
            'slug' => 'product-designer',
            'description' => 'Design useful products.',
            'status' => 'published',
            'submitted_by' => $member->id,
        ]);

        OpportunityApplication::create([
            'opportunity_id' => $opportunity->id,
            'user_id' => $member->id,
            'status' => 'reviewing',
        ]);
        OpportunityApplication::create([
            'opportunity_id' => $opportunity->id,
            'user_id' => $otherMember->id,
            'status' => 'rejected',
        ]);

        $this->actingAs($member)
            ->get(route('applications.index'))
            ->assertOk()
            ->assertSee('Product Designer')
            ->assertSee('Reviewing')
            ->assertDontSee('Rejected');
    }

    public function test_guests_are_redirected_from_application_tracking(): void
    {
        $this->get(route('applications.index'))->assertRedirect(route('login'));
    }
}
