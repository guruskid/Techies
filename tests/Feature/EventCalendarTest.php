<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_event_downloads_as_a_calendar_file(): void
    {
        $event = Event::create([
            'title' => 'Laravel Meetup, Lagos',
            'slug' => 'laravel-meetup-lagos',
            'description' => "A practical evening of Laravel talks.\nBring your questions.",
            'start_date' => '2026-12-01',
            'start_time' => '18:00',
            'end_date' => '2026-12-01',
            'end_time' => '20:30',
            'venue' => 'Tech Hub',
            'city' => 'Lagos',
            'country' => 'Nigeria',
            'status' => 'published',
            'registration_type' => 'free',
        ]);

        $response = $this->get(route('events.calendar', $event->slug));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $response->assertDownload('laravel-meetup-lagos.ics');

        $body = $response->getContent();

        $this->assertStringContainsString('BEGIN:VCALENDAR', $body);
        $this->assertStringContainsString('SUMMARY:Laravel Meetup\, Lagos', $body);
        $this->assertStringContainsString('DTSTART:20261201T180000', $body);
        $this->assertStringContainsString('DTEND:20261201T203000', $body);
        $this->assertStringContainsString('LOCATION:Tech Hub\, Lagos\, Nigeria', $body);
        $this->assertStringContainsString('END:VCALENDAR', $body);
    }

    public function test_online_event_calendar_includes_the_meeting_link_for_attendees(): void
    {
        $event = Event::create([
            'title' => 'Remote Testing Workshop',
            'slug' => 'remote-testing-workshop',
            'start_date' => '2026-12-05',
            'start_time' => '10:00',
            'is_online' => true,
            'meeting_url' => 'https://meet.example.com/testing',
            'status' => 'published',
            'registration_type' => 'free',
        ]);

        $attendee = User::factory()->create(['role' => 'member', 'status' => 'active']);
        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $attendee->id,
            'status' => EventRegistration::STATUS_REGISTERED,
        ]);

        $body = $this->actingAs($attendee)
            ->get(route('events.calendar', $event->slug))
            ->getContent();

        $this->assertStringContainsString('LOCATION:Online - https://meet.example.com/testing', $body);
        $this->assertStringContainsString('Join online: https://meet.example.com/testing', $body);
    }

    public function test_draft_events_have_no_calendar_download(): void
    {
        $event = Event::create([
            'title' => 'Secret Planning Session',
            'slug' => 'secret-planning-session',
            'start_date' => '2026-12-10',
            'status' => 'draft',
            'registration_type' => 'free',
        ]);

        $this->get(route('events.calendar', $event->slug))->assertNotFound();
    }
}
