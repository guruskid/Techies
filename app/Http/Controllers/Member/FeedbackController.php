<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function show(): View
    {
        $feedback = MemberFeedback::where('user_id', auth()->id())->latest()->first();

        return view('member.feedback', compact('feedback'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comments' => ['nullable', 'string', 'max:2000'],
        ]);

        MemberFeedback::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'rating' => $data['rating'],
                'comments' => $data['comments'] ?? null,
                'submitted_at' => now(),
            ]
        );

        return back()->with('success', 'Thank you. Your feedback helps us improve the community.');
    }
}
