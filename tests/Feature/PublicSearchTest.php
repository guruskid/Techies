<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_search_hides_member_only_opportunities_from_guests(): void
    {
        $submitter = User::factory()->create();
        Opportunity::create([
            'title' => 'Open Product Role',
            'slug' => 'open-product-role',
            'description' => 'An open role for product builders.',
            'status' => 'published',
            'is_member_only' => false,
            'submitted_by' => $submitter->id,
        ]);
        Opportunity::create([
            'title' => 'Member Product Role',
            'slug' => 'member-product-role',
            'description' => 'A members-only role for product builders.',
            'status' => 'published',
            'is_member_only' => true,
            'submitted_by' => $submitter->id,
        ]);

        $this->get(route('search', ['q' => 'product']))
            ->assertOk()
            ->assertSee('Open Product Role')
            ->assertDontSee('Member Product Role');
    }

    public function test_search_rejects_queries_that_are_too_short(): void
    {
        $this->get(route('search', ['q' => 'a']))->assertSessionHasErrors('q');
    }
}
