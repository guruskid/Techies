<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_paid_event_registrations_start_unpaid(): void
    {
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);
        $event = $this->paidEvent();

        $this->actingAs($member)
            ->post(route('events.register', $event))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => EventRegistration::STATUS_REGISTERED,
            'payment_status' => EventRegistration::PAYMENT_UNPAID,
        ]);
    }

    public function test_free_event_registrations_do_not_require_payment(): void
    {
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);
        $event = Event::create([
            'title' => 'Free Meetup',
            'slug' => 'free-meetup',
            'start_date' => today()->addWeek()->toDateString(),
            'status' => 'published',
            'registration_type' => 'free',
        ]);

        $this->actingAs($member)->post(route('events.register', $event));

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $member->id,
            'payment_status' => EventRegistration::PAYMENT_NOT_REQUIRED,
        ]);
    }

    public function test_registered_attendees_see_payment_details(): void
    {
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);
        $event = $this->paidEvent();

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => EventRegistration::STATUS_REGISTERED,
            'payment_status' => EventRegistration::PAYMENT_UNPAID,
        ]);

        $this->actingAs($member)
            ->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertSee('Payment pending')
            ->assertSee('https://pay.example.org/workshop')
            ->assertSee('Transfer to Techies Bank');
    }

    public function test_admins_can_mark_a_payment_as_paid_and_refunded(): void
    {
        $admin = $this->admin();
        $event = $this->paidEvent();
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);

        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => EventRegistration::STATUS_REGISTERED,
            'payment_status' => EventRegistration::PAYMENT_UNPAID,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.events.registrations.payment', [$event, $registration]), [
                'payment_status' => EventRegistration::PAYMENT_PAID,
                'payment_reference' => 'TRX-12345',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $registration->refresh();

        $this->assertSame(EventRegistration::PAYMENT_PAID, $registration->payment_status);
        $this->assertSame('TRX-12345', $registration->payment_reference);
        $this->assertNotNull($registration->paid_at);

        $this->actingAs($admin)
            ->post(route('admin.events.registrations.payment', [$event, $registration]), [
                'payment_status' => EventRegistration::PAYMENT_REFUNDED,
                'payment_reference' => 'TRX-12345',
            ])
            ->assertRedirect();

        $registration->refresh();

        $this->assertSame(EventRegistration::PAYMENT_REFUNDED, $registration->payment_status);
        $this->assertNotNull($registration->refunded_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'event.registration.payment_updated',
            'model_id' => $registration->id,
        ]);
    }

    public function test_the_attendee_csv_includes_payment_columns(): void
    {
        $admin = $this->admin();
        $event = $this->paidEvent();
        $member = User::factory()->create(['role' => 'member', 'status' => 'active', 'email' => 'paying@example.org']);

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => EventRegistration::STATUS_REGISTERED,
            'payment_status' => EventRegistration::PAYMENT_PAID,
            'payment_reference' => 'REF-9',
            'paid_at' => now(),
        ]);

        $csv = $this->actingAs($admin)
            ->get(route('admin.events.registrations.export', $event))
            ->streamedContent();

        $this->assertStringContainsString('Payment status', $csv);
        $this->assertStringContainsString('paid', $csv);
        $this->assertStringContainsString('REF-9', $csv);
    }

    private function paidEvent(): Event
    {
        return Event::create([
            'title' => 'Paid Workshop',
            'slug' => 'paid-workshop',
            'start_date' => today()->addWeek()->toDateString(),
            'status' => 'published',
            'registration_type' => 'paid',
            'fee' => 5000,
            'payment_url' => 'https://pay.example.org/workshop',
            'payment_instructions' => 'Transfer to Techies Bank, account 0123456789.',
        ]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');

        return $admin;
    }
}
