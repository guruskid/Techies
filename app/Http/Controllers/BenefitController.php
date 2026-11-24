<?php

namespace App\Http\Controllers;

use App\Models\BenefitRedemption;
use App\Models\MemberBenefit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BenefitController extends Controller
{
    public function index(Request $request): View
    {
        $redeemed = $request->user()
            ? BenefitRedemption::where('user_id', $request->user()->id)->pluck('member_benefit_id')->all()
            : [];

        $benefits = MemberBenefit::active()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->groupBy('category');

        return view('pages.benefits', compact('benefits', 'redeemed'));
    }

    public function redeem(Request $request, MemberBenefit $benefit): RedirectResponse
    {
        abort_unless($benefit->is_active, 404);

        BenefitRedemption::firstOrCreate(
            ['member_benefit_id' => $benefit->id, 'user_id' => $request->user()->id],
            ['redeemed_at' => now()]
        );

        return back()->with('success', 'Benefit marked as used. Your code and instructions are shown below.');
    }
}
