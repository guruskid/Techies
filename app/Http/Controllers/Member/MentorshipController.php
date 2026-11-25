<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\MentorshipRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MentorshipController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $request = $user->mentorshipRequest()->with('match')->first();
        $recommendedMentors = collect();

        if ($request?->role === MentorshipRequest::ROLE_MENTEE || ! $request) {
            $focus = mb_strtolower(implode(' ', array_filter([$request?->expertise, $request?->goals])));
            $recommendedMentors = User::query()
                ->where('status', 'active')
                ->whereKeyNot($user->id)
                ->whereHas('profile', fn ($query) => $query->where('is_public', true))
                ->whereHas('mentorshipRequest', fn ($query) => $query
                    ->where('role', MentorshipRequest::ROLE_MENTOR)
                    ->where('status', MentorshipRequest::STATUS_PENDING))
                ->with(['profile', 'mentorshipRequest'])
                ->limit(30)
                ->get()
                ->map(function (User $mentor) use ($focus): User {
                    $expertise = mb_strtolower((string) $mentor->mentorshipRequest?->expertise);
                    $mentor->setAttribute('mentor_match_score', $focus !== '' && $expertise !== '' && str_contains($focus, $expertise) ? 1 : 0);

                    return $mentor;
                })
                ->sortByDesc('mentor_match_score')
                ->take(4)
                ->values();
        }

        return view('member.mentorship.index', compact('request', 'recommendedMentors'));
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
