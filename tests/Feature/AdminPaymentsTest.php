<?php

namespace Tests\Feature;

use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminPaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_members_cannot_access_the_payment_ledger(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)->get(route('admin.payments.index'))->assertForbidden();
    }

    public function test_admin_refund_requests_are_recorded_as_pending_until_paystack_confirms_them(): void
    {
        config(['services.paystack.secret_key' => 'sk_test_key']);
        Http::fake([
            'https://api.paystack.co/refund' => Http::response([
                'status' => true,
                'message' => 'Refund processing',
                'data' => ['status' => 'pending'],
            ]),
        ]);

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');
        $transaction = PaymentTransaction::create([
            'user_id' => User::factory()->create()->id,
            'reference' => 'PAYMENT-REFERENCE-1',
            'purpose' => 'membership',
            'amount' => 25000,
            'currency' => 'NGN',
            'status' => 'success',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.payments.refund', $transaction), ['reason' => 'Duplicate payment'])
            ->assertRedirect();

        $this->assertSame('refund_pending', $transaction->fresh()->status);
        $this->assertNotNull($transaction->fresh()->refund_reference);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.paystack.co/refund'
            && $request['transaction'] === 'PAYMENT-REFERENCE-1'
            && $request['merchant_note'] === 'Duplicate payment');
    }
}
