<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\IntroRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntroductionController extends Controller
{
    public function index(): View
    {
        $requests = IntroRequest::with('target')
            ->where('requester_id', auth()->id())
            ->latest()
            ->get();

        $members = User::where('status', 'active')
            ->whereKeyNot(auth()->id())
            ->whereHas('profile', fn ($query) => $query->where('is_public', true))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('member.introductions.index', compact('requests', 'members'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'target_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'target_name' => ['nullable', 'string', 'max:200'],
            'purpose' => ['required', 'string', 'max:200'],
            'context' => ['nullable', 'string', 'max:2000'],
        ]);

        if (blank($data['target_user_id']) && blank($data['target_name'])) {
            return back()->withErrors(['target_name' => 'Choose a member or describe who you want to meet.'])->withInput();
        }

        IntroRequest::create([
            'requester_id' => $request->user()->id,
            'target_user_id' => $data['target_user_id'] ?? null,
            'target_name' => $data['target_name'] ?? null,
            'purpose' => $data['purpose'],
            'context' => $data['context'] ?? null,
            'status' => IntroRequest::STATUS_PENDING,
        ]);

        return back()->with('success', 'Your introduction request has been sent to the team.');
    }
}
