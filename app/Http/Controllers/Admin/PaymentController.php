<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Services\PaystackPaymentService;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'status' => ['nullable', 'in:pending,success,failed,refund_pending,refund_needs_attention,refunded'],
            'purpose' => ['nullable', 'in:membership,event'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $query = PaymentTransaction::with('user');

        if (filled($data['status'] ?? null)) {
            $query->where('status', $data['status']);
        }

        if (filled($data['purpose'] ?? null)) {
            $query->where('purpose', $data['purpose']);
        }

        if (filled($data['search'] ?? null)) {
            $search = $data['search'];
            $query->where(function ($transactions) use ($search): void {
                $transactions->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        return view('admin.payments.index', [
            'transactions' => $query->latest()->paginate(25)->withQueryString(),
            'counts' => PaymentTransaction::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'filters' => $data,
        ]);
    }

    public function refund(Request $request, PaymentTransaction $paymentTransaction, PaystackPaymentService $payments): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $payments->refund($paymentTransaction, $data['reason']);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['refund' => $exception->getMessage()]);
        }

        AuditLogger::log('payment.refund_requested', $paymentTransaction, [
            'reference' => $paymentTransaction->reference,
            'reason' => $data['reason'],
        ]);

        return back()->with('success', 'Refund request sent to Paystack. The transaction will update after Paystack processes it.');
    }
}
