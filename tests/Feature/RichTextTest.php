<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Support\RichText;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RichTextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_unsafe_markup_is_removed(): void
    {
        $clean = RichText::clean(
            '<p onclick="steal()">Hello <strong>world</strong></p>'
            .'<script>alert(1)</script>'
            .'<a href="javascript:alert(1)">bad link</a>'
        );

        $this->assertStringContainsString('<strong>world</strong>', $clean);
        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
    }

    public function test_editor_html_is_stored_and_rendered_on_event_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)->post(route('admin.events.store'), [
            'title' => 'Rich Event',
            'start_date' => today()->addWeek()->toDateString(),
            'registration_type' => 'free',
            'status' => 'published',
            'description' => '<p>Join us for <strong>great talks</strong>.</p><script>alert(1)</script>',
            'agenda' => '<ul><li>Opening</li><li>Panel</li></ul>',
        ])->assertRedirect(route('admin.events.index'));

        $event = Event::firstOrFail();

        $this->assertStringContainsString('<strong>great talks</strong>', $event->description);
        $this->assertStringNotContainsString('<script>', $event->description);

        $this->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertSee('<strong>great talks</strong>', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_legacy_plain_text_still_renders_as_paragraphs(): void
    {
        $event = Event::create([
            'title' => 'Plain Event',
            'slug' => 'plain-event',
            'start_date' => today()->addWeek()->toDateString(),
            'registration_type' => 'free',
            'status' => 'published',
            'description' => "First line\n\nSecond line",
        ]);

        $this->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertSee('First line')
            ->assertSee('Second line');
    }
}
