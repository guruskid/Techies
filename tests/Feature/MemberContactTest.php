<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\MemberContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class MemberContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_member_can_email_another_member_through_their_profile(): void
    {
        Notification::fake();

        $sender = $this->member();
        $recipient = $this->member();
        $recipient->ensureProfile()->update(['username' => 'ada-lovelace', 'is_public' => true]);

        $this->actingAs($sender)
            ->post(route('members.contact', 'ada-lovelace'), [
                'subject' => 'Speaking at your meetup',
                'message' => 'Hello Ada, would you speak at our next chapter event?',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertSentTo($recipient, MemberContactMessage::class);
    }

    public function test_guests_can_contact_a_member_with_their_name_and_email(): void
    {
        Notification::fake();

        $recipient = $this->member();
        $recipient->ensureProfile()->update(['username' => 'ada-lovelace', 'is_public' => true]);

        $this->post(route('members.contact', 'ada-lovelace'), [
            'name' => 'Guest Sender',
            'email' => 'guest@example.org',
            'subject' => 'Hello',
            'message' => 'Just saying hello.',
        ])->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertSentTo($recipient, MemberContactMessage::class);
    }

    public function test_members_cannot_message_themselves(): void
    {
        Notification::fake();

        $member = $this->member();
        $member->ensureProfile()->update(['username' => 'ada-lovelace', 'is_public' => true]);

        $this->actingAs($member)
            ->post(route('members.contact', 'ada-lovelace'), [
                'subject' => 'Hello me',
                'message' => 'Talking to myself.',
            ])
            ->assertForbidden();

        Notification::assertNothingSent();
    }

    public function test_the_honeypot_silently_discards_bot_submissions(): void
    {
        Notification::fake();

        $sender = $this->member();
        $recipient = $this->member();
        $recipient->ensureProfile()->update(['username' => 'ada-lovelace', 'is_public' => true]);

        $this->actingAs($sender)
            ->post(route('members.contact', 'ada-lovelace'), [
                'subject' => 'Buy cheap followers',
                'message' => 'Spam spam spam.',
                'website' => 'https://spam.example',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertNothingSent();
    }

    public function test_the_contact_form_requires_a_subject_and_message(): void
    {
        $sender = $this->member();
        $recipient = $this->member();
        $recipient->ensureProfile()->update(['username' => 'ada-lovelace', 'is_public' => true]);

        $this->actingAs($sender)
            ->post(route('members.contact', 'ada-lovelace'), [])
            ->assertSessionHasErrors(['subject', 'message']);
    }

    public function test_the_public_profile_shows_the_contact_form_to_signed_in_members(): void
    {
        $sender = $this->member();
        $recipient = $this->member();
        $recipient->ensureProfile()->update(['username' => 'ada-lovelace', 'is_public' => true]);

        $this->actingAs($sender)
            ->get(route('members.show', 'ada-lovelace'))
            ->assertOk()
            ->assertSee('Contact '.Str::before($recipient->name, ' '))
            ->assertSee('Send message');
    }

    private function member(): User
    {
        return User::factory()->create(['role' => 'member', 'status' => 'active']);
    }
}
