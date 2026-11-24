<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Notifications\MembershipPaymentSubmitted;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipController extends Controller
{
    public function show(Request $request): View
    {
        $fee = (int) Setting::value('membership_fee', '15000');
        $currency = (string) Setting::value('membership_currency', 'NGN');

        return view('pages.membership', [
            'fee' => $fee,
            'currency' => $currency,
            'paymentUrl' => Setting::value('membership_payment_url'),
            'instructions' => Setting::value('membership_payment_instructions'),
            'benefits' => $this->benefits(),
            'guarantee' => Setting::value('membership_guarantee'),
            'user' => $request->user(),
        ]);
    }

    public function submitPayment(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user->hasActiveMembership(), 403, 'Your membership is already active.');

        $data = $request->validate([
            'membership_reference' => ['required', 'string', 'max:120'],
        ]);

        $user->update([
            'membership_status' => 'pending',
            'membership_reference' => $data['membership_reference'],
        ]);

        AuditLogger::log('membership.payment_submitted', $user, [
            'reference' => $data['membership_reference'],
        ]);

        User::reviewers()
            ->whereKeyNot($user->id)
            ->get()
            ->each(fn (User $reviewer) => $reviewer->notify(new MembershipPaymentSubmitted($user)));

        return redirect()
            ->route('membership.show')
            ->with('success', 'Payment details received. The team will confirm your membership and email you.');
    }

    /**
     * @return array<int, string>
     */
    private function benefits(): array
    {
        $default = implode("\n", [
            'Member events, workshops and meetups',
            'A public profile that showcases your work',
            'Mentorship and peer learning circles',
            'Opportunity applications and career support',
            'Speaker calls, volunteering and chapter membership',
        ]);

        return collect(preg_split('/[\r\n]+/', (string) Setting::value('membership_benefits', $default)) ?: [])
            ->map(fn (string $benefit): string => trim($benefit))
            ->filter()
            ->values()
            ->all();
    }
}
