<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventSpeakerImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('public');
    }

    public function test_admins_can_upload_photos_for_several_speakers(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)->post(route('admin.events.store'), [
            'title' => 'Speakers Night',
            'start_date' => today()->addWeek()->toDateString(),
            'registration_type' => 'free',
            'status' => 'published',
            'speaker_images' => [
                UploadedFile::fake()->image('ada.jpg', 800, 800),
                UploadedFile::fake()->image('grace.jpg', 800, 800),
            ],
        ])->assertRedirect(route('admin.events.index'));

        $event = Event::firstOrFail();

        $this->assertCount(2, $event->speaker_images);

        foreach ($event->speaker_images as $path) {
            $this->assertStringStartsWith('/storage/events/speakers/', $path);
            Storage::disk('public')->assertExists(str_replace('/storage/', '', $path));
        }
    }

    public function test_speaker_photos_are_optional(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)->post(route('admin.events.store'), [
            'title' => 'No Photo Yet',
            'start_date' => today()->addWeek()->toDateString(),
            'registration_type' => 'free',
            'status' => 'draft',
        ])->assertRedirect(route('admin.events.index'));

        $this->assertSame([], Event::firstOrFail()->speaker_images ?? []);
    }

    public function test_editing_keeps_existing_photos_and_can_add_or_remove_some(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $event = Event::create([
            'title' => 'Speaker Gallery',
            'slug' => 'speaker-gallery',
            'start_date' => today()->addWeek()->toDateString(),
            'registration_type' => 'free',
            'status' => 'draft',
            'speaker_images' => [
                '/storage/events/speakers/keep.jpg',
                '/storage/events/speakers/remove.jpg',
            ],
        ]);

        $this->actingAs($admin)->patch(route('admin.events.update', $event), [
            'title' => 'Speaker Gallery',
            'start_date' => $event->start_date->toDateString(),
            'registration_type' => 'free',
            'status' => 'draft',
            'remove_speaker_images' => ['/storage/events/speakers/remove.jpg'],
            'speaker_images' => [
                UploadedFile::fake()->image('new.jpg', 800, 800),
            ],
        ])->assertRedirect(route('admin.events.index'));

        $images = $event->fresh()->speaker_images;

        $this->assertCount(2, $images);
        $this->assertContains('/storage/events/speakers/keep.jpg', $images);
        $this->assertNotContains('/storage/events/speakers/remove.jpg', $images);
    }

    public function test_editing_without_new_photos_keeps_the_existing_gallery(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $event = Event::create([
            'title' => 'Existing Photos',
            'slug' => 'existing-photos',
            'start_date' => today()->addWeek()->toDateString(),
            'registration_type' => 'free',
            'status' => 'draft',
            'speaker_images' => ['/storage/events/speakers/old-one.jpg', '/storage/events/speakers/old-two.jpg'],
        ]);

        $this->actingAs($admin)->patch(route('admin.events.update', $event), [
            'title' => 'Existing Photos',
            'start_date' => $event->start_date->toDateString(),
            'registration_type' => 'free',
            'status' => 'draft',
        ])->assertRedirect(route('admin.events.index'));

        $this->assertSame(
            ['/storage/events/speakers/old-one.jpg', '/storage/events/speakers/old-two.jpg'],
            $event->fresh()->speaker_images,
        );
    }

    public function test_all_speaker_photos_are_shown_on_the_event_page(): void
    {
        $event = Event::create([
            'title' => 'Photo On Page',
            'slug' => 'photo-on-page',
            'start_date' => today()->addWeek()->toDateString(),
            'registration_type' => 'free',
            'status' => 'published',
            'key_speakers' => "Ada Lovelace\nGrace Hopper",
            'speaker_images' => ['/storage/events/speakers/ada.jpg', '/storage/events/speakers/grace.jpg'],
        ]);

        $this->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertSee('/storage/events/speakers/ada.jpg', false)
            ->assertSee('/storage/events/speakers/grace.jpg', false)
            ->assertSee('Ada Lovelace')
            ->assertSee('Grace Hopper');
    }
}
