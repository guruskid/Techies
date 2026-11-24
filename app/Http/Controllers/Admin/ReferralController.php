<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralOffer;
use App\Models\ReferralRequest;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function index(): View
    {
        $offers = ReferralOffer::with('user', 'opportunity')->latest()->paginate(20);
        $requests = ReferralRequest::with('requester', 'offer.opportunity', 'offer.user')->latest()->paginate(20, ['*'], 'requests');

        return view('admin.referrals.index', compact('offers', 'requests'));
    }
}
