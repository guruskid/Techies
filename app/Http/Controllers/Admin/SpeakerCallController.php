<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Event;
use App\Models\SpeakerCall;
use App\Models\SpeakerSubmission;
use App\Models\User;
use App\Notifications\SpeakerSubmissionApproved;
use App\Notifications\SpeakerSubmissionRejected;
use App\Support\AuditLogger;
use App\Support\RichText;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SpeakerCallController extends Controller
{
    public function index(Request $request): View
    {
        $query = SpeakerCall::with(['event', 'hub', 'chapter'])
            ->withCount('submissions')
            ->latest();

        $this->scopeToUser($query, $request->user());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return view('admin.speaker-calls.index', [
            'speakerCalls' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.speaker-calls.create', [
            'speakerCall' => new SpeakerCall(['status' => SpeakerCall::STATUS_OPEN]),
            'events' => $this->manageableEvents(auth()->user()),
            'topicsText' => '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $event = $this->eventForUser($data['event_id'] ?? null, $request->user());
        [$hubId, $chapterId] = $this->ownership($request->user(), $event);

        $speakerCall = SpeakerCall::create([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['title']),
            'description' => $data['description'] ?? null,
            'topics' => $data['topics'],
            'event_id' => $event?->id,
            'hub_id' => $hubId,
            'chapter_id' => $chapterId,
            'created_by' => $request->user()->id,
            'opens_at' => $data['opens_at'] ?? null,
            'closes_at' => $data['closes_at'] ?? null,
            'status' => $data['status'],
        ]);

        AuditLogger::log('speaker_call.created', $speakerCall, ['title' => $speakerCall->title]);

        return redirect()->route('admin.speaker-calls.index')
            ->with('success', 'Call for speakers created.');
    }

    public function edit(SpeakerCall $speakerCall): View
    {
        $this->ensureCanManage($speakerCall);

        return view('admin.speaker-calls.edit', [
            'speakerCall' => $speakerCall,
            'events' => $this->manageableEvents(auth()->user()),
            'topicsText' => implode("\n", $speakerCall->topicList()),
        ]);
    }

    public function update(Request $request, SpeakerCall $speakerCall): RedirectResponse
    {
        $this->ensureCanManage($speakerCall);

        $data = $this->validated($request);
        $event = $this->eventForUser($data['event_id'] ?? null, $request->user());
        [$hubId, $chapterId] = $this->ownership($request->user(), $event);

        $speakerCall->update([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['title'], $speakerCall->id),
            'description' => $data['description'] ?? null,
            'topics' => $data['topics'],
            'event_id' => $event?->id,
            'hub_id' => $hubId,
            'chapter_id' => $chapterId,
            'opens_at' => $data['opens_at'] ?? null,
            'closes_at' => $data['closes_at'] ?? null,
            'status' => $data['status'],
        ]);

        AuditLogger::log('speaker_call.updated', $speakerCall, ['title' => $speakerCall->title]);

        return redirect()->route('admin.speaker-calls.index')
            ->with('success', 'Call for speakers updated.');
    }

    public function submissions(SpeakerCall $speakerCall): View
    {
        $this->ensureCanManage($speakerCall);

        return view('admin.speaker-calls.submissions', [
            'speakerCall' => $speakerCall,
            'submissions' => $speakerCall->submissions()
                ->with('user', 'reviewer')
                ->orderByRaw("CASE status WHEN 'pending' THEN 0 ELSE 1 END")
                ->latest()
                ->get(),
        ]);
    }

    public function review(Request $request, SpeakerCall $speakerCall, SpeakerSubmission $submission): RedirectResponse
    {
        $this->ensureCanManage($speakerCall);
        abort_unless($submission->speaker_call_id === $speakerCall->id, 404);

        $data = $request->validate([
            'status' => ['required', Rule::in([SpeakerSubmission::STATUS_APPROVED, SpeakerSubmission::STATUS_REJECTED])],
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $previous = $submission->status;

        $submission->update([
            'status' => $data['status'],
            'review_notes' => $data['review_notes'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        AuditLogger::log('speaker_call.submission.reviewed', $submission, [
            'call' => $speakerCall->title,
            'from' => $previous,
            'to' => $data['status'],
        ]);

        if ($previous !== $data['status']) {
            $notification = $data['status'] === SpeakerSubmission::STATUS_APPROVED
                ? new SpeakerSubmissionApproved($submission)
                : new SpeakerSubmissionRejected($submission);

            $submission->user?->notify($notification);
        }

        return back()->with('success', 'Submission '.$data['status'].'. The speaker has been emailed.');
    }

    public function destroy(SpeakerCall $speakerCall): RedirectResponse
    {
        $this->ensureCanManage($speakerCall);

        $speakerCall->delete();

        AuditLogger::log('speaker_call.deleted', $speakerCall, ['title' => $speakerCall->title]);

        return redirect()->route('admin.speaker-calls.index')
            ->with('success', 'Call for speakers deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:20000'],
            'topics_text' => ['required', 'string', 'max:5000'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date', 'after_or_equal:opens_at'],
            'status' => ['required', Rule::in(array_keys(SpeakerCall::statuses()))],
        ]);

        $topics = collect(preg_split('/[\r\n,]+/', $data['topics_text']) ?: [])
            ->map(fn (string $topic): string => trim($topic))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($topics === []) {
            throw ValidationException::withMessages([
                'topics_text' => 'Add at least one topic speakers can choose from.',
            ]);
        }

        $data['topics'] = $topics;
        $data['description'] = RichText::clean($data['description'] ?? null);

        unset($data['topics_text']);

        return $data;
    }

    /**
     * @return array<int, Event>
     */
    private function manageableEvents(User $user)
    {
        $query = Event::query()->orderByDesc('start_date');

        if (! $user->isAdmin() && ! $user->isSuperAdmin()) {
            $chapterIds = $this->coordinatedChapterIds($user);
            $hubId = $user->hub?->id;

            $query->where(function (Builder $builder) use ($user, $hubId, $chapterIds): void {
                $builder->where('organizer_id', $user->id);

                if ($hubId) {
                    $builder->orWhere('hub_id', $hubId);
                }

                if ($chapterIds !== []) {
                    $builder->orWhereIn('chapter_id', $chapterIds);
                }
            });
        }

        return $query->get();
    }

    private function eventForUser(?int $eventId, User $user): ?Event
    {
        if (! $eventId) {
            return null;
        }

        $event = Event::findOrFail($eventId);

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return $event;
        }

        if ((int) $event->organizer_id === (int) $user->id) {
            return $event;
        }

        if ($event->hub_id && (int) $event->hub_id === (int) $user->hub?->id) {
            return $event;
        }

        if ($event->chapter_id && in_array((int) $event->chapter_id, $this->coordinatedChapterIds($user), true)) {
            return $event;
        }

        abort(403, 'You cannot attach a call to that event.');
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    private function ownership(User $user, ?Event $event): array
    {
        if ($event) {
            return [$event->hub_id, $event->chapter_id];
        }

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return [null, null];
        }

        if ($user->hasRole('hub_owner')) {
            return [$user->hub?->id, null];
        }

        return [null, $this->coordinatedChapterIds($user)[0] ?? null];
    }

    private function scopeToUser(Builder $query, User $user): void
    {
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return;
        }

        $hubId = $user->hub?->id;
        $chapterIds = $this->coordinatedChapterIds($user);

        $query->where(function (Builder $builder) use ($user, $hubId, $chapterIds): void {
            $builder->where('created_by', $user->id);

            if ($hubId) {
                $builder->orWhere('hub_id', $hubId);
            }

            if ($chapterIds !== []) {
                $builder->orWhereIn('chapter_id', $chapterIds);
            }
        });
    }

    private function ensureCanManage(SpeakerCall $speakerCall): void
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return;
        }

        if ((int) $speakerCall->created_by === (int) $user->id) {
            return;
        }

        if ($speakerCall->hub_id && (int) $speakerCall->hub_id === (int) $user->hub?->id) {
            return;
        }

        if ($speakerCall->chapter_id && in_array((int) $speakerCall->chapter_id, $this->coordinatedChapterIds($user), true)) {
            return;
        }

        abort(403);
    }

    /**
     * @return array<int, int>
     */
    private function coordinatedChapterIds(User $user): array
    {
        return Chapter::query()
            ->where('coordinator_id', $user->id)
            ->orWhere('second_coordinator_id', $user->id)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'speaker-call';
        $candidate = $base;
        $suffix = 2;

        while (SpeakerCall::withTrashed()
            ->where('slug', $candidate)
            ->when($ignoreId, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
