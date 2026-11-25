<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Hub;
use App\Models\Opportunity;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_hubs_are_hidden_from_the_public_directory(): void
    {
        $owner = User::factory()->create();

        Hub::create([
            'user_id' => $owner->id,
            'name' => 'Pending Hub',
            'slug' => 'pending-hub',
            'status' => 'pending',
            'is_verified' => false,
            'is_public' => false,
        ]);

        $this->get('/hubs/pending-hub')->assertNotFound();
        $this->get('/hubs')->assertDontSee('Pending Hub');
    }

    public function test_approved_hubs_are_visible(): void
    {
        $owner = User::factory()->create();

        Hub::create([
            'user_id' => $owner->id,
            'name' => 'Approved Hub',
            'slug' => 'approved-hub',
            'status' => 'approved',
            'is_verified' => true,
            'is_public' => true,
        ]);

        $this->get('/hubs/approved-hub')->assertOk();
    }

    public function test_hub_directory_can_filter_by_organization_type(): void
    {
        $owner = User::factory()->create();

        Hub::create([
            'user_id' => $owner->id,
            'name' => 'Enugu Incubator',
            'slug' => 'enugu-incubator',
            'organization_type' => 'Incubator',
            'status' => 'approved',
            'is_verified' => true,
            'is_public' => true,
        ]);
        Hub::create([
            'user_id' => User::factory()->create()->id,
            'name' => 'Lagos Developer Hub',
            'slug' => 'lagos-developer-hub',
            'organization_type' => 'Developer Community',
            'status' => 'approved',
            'is_verified' => true,
            'is_public' => true,
        ]);

        $this->get(route('hubs', ['organization_type' => 'Incubator']))
            ->assertOk()
            ->assertSee('Enugu Incubator')
            ->assertDontSee('Lagos Developer Hub');
    }

    public function test_suspended_members_and_private_profiles_are_not_public(): void
    {
        $suspended = User::factory()->create(['status' => 'suspended']);
        UserProfile::create([
            'user_id' => $suspended->id,
            'username' => 'suspended-member',
            'is_public' => true,
        ]);

        $privateMember = User::factory()->create();
        UserProfile::create([
            'user_id' => $privateMember->id,
            'username' => 'private-member',
            'is_public' => false,
        ]);

        $this->get('/members/suspended-member')->assertNotFound();
        $this->get('/members/private-member')->assertForbidden();
        $this->get('/members')->assertDontSee('suspended-member');
    }

    public function test_draft_opportunities_and_inactive_chapters_are_not_public(): void
    {
        Opportunity::create([
            'title' => 'Draft Role',
            'slug' => 'draft-role',
            'status' => 'draft',
        ]);

        Chapter::create([
            'name' => 'Inactive Chapter',
            'slug' => 'inactive-chapter',
            'status' => 'inactive',
        ]);

        $this->get('/opportunities/draft-role')->assertNotFound();
        $this->get('/chapters/inactive-chapter')->assertNotFound();
    }
}
