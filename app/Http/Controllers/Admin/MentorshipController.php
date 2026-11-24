<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MentorshipRequest;
use App\Notifications\MentorshipMatched;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MentorshipController extends Controller
{
    public function index(): View
    {
        $mentors = MentorshipRequest::with('user.profile')
            ->where('role', MentorshipRequest::ROLE_MENTOR)
            ->whereIn('status', [MentorshipRequest::STATUS_PENDING, MentorshipRequest::STATUS_MATCHED])
            ->latest()
            ->get();

        $mentees = MentorshipRequest::with('user.profile')
            ->where('role', MentorshipRequest::ROLE_MENTEE)
            ->whereIn('status', [MentorshipRequest::STATUS_PENDING, MentorshipRequest::STATUS_MATCHED])
            ->latest()
            ->get();

        $matched = MentorshipRequest::with(['user', 'match'])
            ->where('status', MentorshipRequest::STATUS_MATCHED)
            ->where('role', MentorshipRequest::ROLE_MENTEE)
            ->latest('matched_at')
            ->get();

        return view('admin.mentorship.index', compact('mentors', 'mentees', 'matched'));
    }

    public function match(Request $request, MentorshipRequest $mentorshipRequest): RedirectResponse
    {
        abort_unless($mentorshipRequest->role === MentorshipRequest::ROLE_MENTEE, 404);
        abort_unless($mentorshipRequest->status === MentorshipRequest::STATUS_PENDING, 422, 'This request is already matched or closed.');

        $data = $request->validate([
            'mentor_request_id' => ['required', 'integer', 'exists:mentorship_requests,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $mentor = MentorshipRequest::with('user')->findOrFail($data['mentor_request_id']);

        abort_unless($mentor->role === MentorshipRequest::ROLE_MENTOR, 422, 'Choose a mentor request.');
        abort_unless($mentor->status === MentorshipRequest::STATUS_PENDING, 422, 'That mentor is already matched.');

        $mentorshipRequest->update([
            'status' => MentorshipRequest::STATUS_MATCHED,
            'matched_with' => $mentor->user_id,
            'matched_at' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        $mentor->update([
            'status' => MentorshipRequest::STATUS_MATCHED,
            'matched_with' => $mentorshipRequest->user_id,
            'matched_at' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        AuditLogger::log('mentorship.matched', $mentorshipRequest, [
            'mentee' => $mentorshipRequest->user?->name,
            'mentor' => $mentor->user?->name,
        ]);

        $mentorshipRequest->user?->notify(new MentorshipMatched($mentorshipRequest, $mentor->user, MentorshipRequest::ROLE_MENTEE));
        $mentor->user?->notify(new MentorshipMatched($mentor, $mentorshipRequest->user, MentorshipRequest::ROLE_MENTOR));

        return back()->with('success', 'Mentorship matched. Both members have been emailed.');
    }

    public function close(MentorshipRequest $mentorshipRequest): RedirectResponse
    {
        $counterpart = $mentorshipRequest->matched_with
            ? MentorshipRequest::where('user_id', $mentorshipRequest->matched_with)->first()
            : null;

        $mentorshipRequest->update(['status' => MentorshipRequest::STATUS_CLOSED]);
        $counterpart?->update(['status' => MentorshipRequest::STATUS_CLOSED]);

        AuditLogger::log('mentorship.closed', $mentorshipRequest, ['pair' => $mentorshipRequest->id]);

        return back()->with('success', 'Mentorship cycle closed.');
    }
}
