<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use App\Models\ReferralOffer;
use App\Models\ReferralRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function index(): View
    {
        $offers = ReferralOffer::with('opportunity', 'requests.requester')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $requests = ReferralRequest::with('offer.opportunity', 'offer.user')
            ->where('requester_id', auth()->id())
            ->latest()
            ->get();

        return view('member.referrals.index', compact('offers', 'requests'));
    }

    public function storeOffer(Request $request, Opportunity $opportunity): RedirectResponse
    {
        ReferralOffer::updateOrCreate(
            ['user_id' => $request->user()->id, 'opportunity_id' => $opportunity->id],
            ['note' => $request->input('note'), 'status' => 'open']
        );

        return back()->with('success', 'You are now offering referrals for this opportunity.');
    }

    public function requestReferral(Request $request, ReferralOffer $offer): RedirectResponse
    {
        abort_if($offer->user_id === $request->user()->id, 403);

        $data = $request->validate(['message' => ['required', 'string', 'max:1500']]);

        ReferralRequest::firstOrCreate(
            ['referral_offer_id' => $offer->id, 'requester_id' => $request->user()->id],
            ['message' => $data['message'], 'status' => 'pending']
        );

        return back()->with('success', 'Your referral request has been sent.');
    }

    public function respond(Request $request, ReferralRequest $referralRequest): RedirectResponse
    {
        abort_unless($referralRequest->offer?->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'status' => ['required', 'in:accepted,declined'],
            'response_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $referralRequest->update([
            'status' => $data['status'],
            'response_note' => $data['response_note'] ?? null,
            'responded_at' => now(),
        ]);

        return back()->with('success', 'Referral request updated.');
    }
}
