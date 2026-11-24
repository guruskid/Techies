<?php

namespace App\Http\Controllers;

use App\Models\SpeakerCall;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SpeakerCallController extends Controller
{
    public function index(): View
    {
        $speakerCalls = SpeakerCall::with(['event', 'hub', 'chapter'])
            ->withCount([
                'submissions',
                'submissions as approved_submissions_count' => fn ($query) => $query->where('status', 'approved'),
            ])
            ->where('status', SpeakerCall::STATUS_OPEN)
            ->where(function ($query): void {
                $query->whereNull('opens_at')->orWhereDate('opens_at', '<=', today());
            })
            ->where(function ($query): void {
                $query->whereNull('closes_at')->orWhereDate('closes_at', '>=', today());
            })
            ->orderByRaw('closes_at IS NULL, closes_at')
            ->latest()
            ->paginate(12);

        return view('pages.speaker-calls', compact('speakerCalls'));
    }

    public function show(SpeakerCall $speakerCall): View
    {
        abort_if($speakerCall->status === SpeakerCall::STATUS_DRAFT, 404);

        $speakerCall->load([
            'event',
            'hub',
            'chapter',
            'submissions' => fn ($query) => $query->where('status', 'approved')->with('user.profile'),
        ]);

        $submission = auth()->check()
            ? $speakerCall->submissions()->where('user_id', auth()->id())->first()
            : null;

        return view('pages.speaker-call-detail', compact('speakerCall', 'submission'));
    }

    public function submit(Request $request, SpeakerCall $speakerCall): RedirectResponse
    {
        abort_unless($speakerCall->isOpen(), 403, 'This call is not accepting submissions.');

        $data = $request->validate([
            'topic' => ['required', 'string', Rule::in($speakerCall->topicList())],
            'title' => ['required', 'string', 'max:160'],
            'abstract' => ['required', 'string', 'max:2000'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($speakerCall->submissions()->where('user_id', $request->user()->id)->exists()) {
            return back()->with('error', 'You have already submitted a proposal for this call.');
        }

        $speakerCall->submissions()->create([
            'user_id' => $request->user()->id,
            'topic' => $data['topic'],
            'title' => $data['title'],
            'abstract' => $data['abstract'],
            'bio' => $data['bio'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('speaker-calls.show', $speakerCall->slug)
            ->with('success', 'Your proposal has been submitted. The organiser will email you once it is reviewed.');
    }
}
