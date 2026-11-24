<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\MentorshipRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MentorshipController extends Controller
{
    public function index(): View
    {
        $request = auth()->user()->mentorshipRequest()->with('match')->first();

        return view('member.mentorship.index', compact('request'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $existing = $user->mentorshipRequest;

        if ($existing?->isMatched()) {
            return back()->with('error', 'You already have an active mentorship match. Contact the team if you need to change it.');
        }

        $data = $request->validate([
            'role' => ['required', 'in:mentor,mentee'],
            'expertise' => ['nullable', 'string', 'max:255'],
            'goals' => ['nullable', 'string', 'max:2000'],
            'availability' => ['nullable', 'string', 'max:255'],
        ]);

        MentorshipRequest::updateOrCreate(
            ['user_id' => $user->id],
            [
                'role' => $data['role'],
                'expertise' => $data['expertise'] ?? null,
                'goals' => $data['goals'] ?? null,
                'availability' => $data['availability'] ?? null,
                'status' => MentorshipRequest::STATUS_PENDING,
                'matched_with' => null,
                'matched_at' => null,
            ]
        );

        return back()->with('success', 'Your mentorship request has been saved. The team will email you when a match is ready.');
    }
}
