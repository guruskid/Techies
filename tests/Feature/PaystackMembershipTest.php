<?php

namespace Tests\Feature;

use App\Models\PaymentTransaction;
use App\Models\User;
use App\Notifications\MembershipActivated;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaystackMembershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        config(['services.paystack.secret_key' => 'sk_test_membership']);
    }

    public function test_membership_page_uses_paystack_checkout_when_configured(): void
    {
        $member = $this->member();

        $this->actingAs($member)->get(route('membership.show'))
            ->assertOk()
            ->assertSee('Pay ₦15,000 securely')
            ->assertDontSee('I have paid');
    }

    public function test_initialize_redirects_to_paystack_checkout(): void
    {
        Http::fake([
            'https://api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/test-reference'],
            ]),
        ]);

        $member = $this->member();

        $this->actingAs($member)
            ->post(route('membership.payment.initialize'))
            ->assertRedirect('https://checkout.paystack.com/test-reference');

        $this->assertDatabaseHas('payment_transactions', [
            'user_id' => $member->id,
            'purpose' => 'membership',
            'amount' => 15000,
            'currency' => 'NGN',
            'status' => 'pending',
        ]);
    }

    public function test_callback_verifies_payment_and_activates_membership(): void
    {
        Notification::fake();

        $member = $this->member();
        $transaction = $this->transaction($member);

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => $transaction->reference,
                    'amount' => 1500000,
                    'currency' => 'NGN',
                ],
            ]),
        ]);

        $this->get(route('membership.payment.callback', ['reference' => $transaction->reference]))
            ->assertRedirect(route('membership.show'));

        $this->assertSame('active', $member->fresh()->membership_status);
        $this->assertSame('success', $transaction->fresh()->status);
        $this->assertTrue($transaction->fresh()->metadata['payment_applied']);

        Notification::assertSentTo($member, MembershipActivated::class);
    }

    public function test_webhook_activates_membership_without_callback(): void
    {
        Notification::fake();

        $member = $this->member();
        $transaction = $this->transaction($member);

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => $transaction->reference,
                    'amount' => 1500000,
                    'currency' => 'NGN',
                ],
            ]),
        ]);

        $payload = json_encode([
            'event' => 'charge.success',
            'data' => ['reference' => $transaction->reference],
        ]);

        $this->postJson(route('payments.paystack.webhook'), json_decode($payload, true), [
            'x-paystack-signature' => hash_hmac('sha512', $payload, 'sk_test_membership'),
        ])->assertOk();

        $this->assertSame('active', $member->fresh()->membership_status);
        $this->assertTrue($transaction->fresh()->metadata['payment_applied']);

        Notification::assertSentTo($member, MembershipActivated::class);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $member = $this->member();
        $transaction = $this->transaction($member);

        $this->postJson(route('payments.paystack.webhook'), [
            'event' => 'charge.success',
            'data' => ['reference' => $transaction->reference],
        ], [
            'x-paystack-signature' => str_repeat('0', 128),
        ])->assertUnauthorized();

        $this->assertSame('unpaid', $member->fresh()->membership_status);
    }

    private function member(): User
    {
        return User::factory()->create([
            'role' => 'member',
            'status' => 'active',
            'membership_status' => 'unpaid',
        ]);
    }

    private function transaction(User $member): PaymentTransaction
    {
        return PaymentTransaction::create([
            'user_id' => $member->id,
            'reference' => 'MWTT-TEST-'.strtoupper(uniqid()),
            'purpose' => 'membership',
            'amount' => 15000,
            'currency' => 'NGN',
            'status' => 'pending',
        ]);
    }
}
