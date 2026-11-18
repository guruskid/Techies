<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactAndNewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_sends_a_message(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Test Sender',
            'email' => 'sender@example.com',
            'subject' => 'Partnership',
            'message' => 'I would like to partner with MWTT.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_contact_form_validates_required_fields(): void
    {
        $this->post('/contact', [])
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_newsletter_requires_a_valid_email(): void
    {
        $this->post('/newsletter', ['email' => 'not-an-email'])
            ->assertSessionHasErrors('email');
    }

    public function test_newsletter_subscription_is_stored_once(): void
    {
        $payload = ['name' => 'Subscriber', 'email' => 'subscriber@example.com'];

        $this->post('/newsletter', $payload)->assertSessionHas('success');
        $this->post('/newsletter', $payload)->assertSessionHas('success');

        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }
}
