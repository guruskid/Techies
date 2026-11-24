<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\User;
use App\Notifications\ChapterCoordinatorAssigned;
use App\Notifications\ChapterCoordinatorRemoved;
use App\Support\AuditLogger;
use App\Support\RichText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChapterController extends Controller
{
    public function index(Request $request): View
    {
        $query = Chapter::with('coordinator');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('admin.chapters.index', [
            'chapters' => $query->latest()->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.chapters.create', [
            'coordinators' => User::where('status', 'active')->orderBy('name')->get(['id', 'name', 'email']),
            'usedStates' => Chapter::whereNotNull('state')->pluck('state')->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $chapter = Chapter::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['slug'] ?? null, $data['name']),
            'country' => $data['country'] ?? null,
            'state' => $data['state'] ?? null,
            'city' => $data['city'] ?? null,
            'description' => $data['description'] ?? null,
            'coordinator_id' => $data['coordinator_id'] ?? null,
            'second_coordinator_id' => $data['second_coordinator_id'] ?? null,
            'status' => $data['status'],
        ]);

        $this->notifyCoordinators($chapter, [], [$chapter->coordinator_id, $chapter->second_coordinator_id]);

        AuditLogger::log('chapter.created', $chapter, ['name' => $chapter->name]);

        return redirect()->route('admin.chapters.index')->with('success', 'Chapter created.');
    }

    public function edit(Chapter $chapter): View
    {
        return view('admin.chapters.edit', [
            'chapter' => $chapter,
            'coordinators' => User::where('status', 'active')->orderBy('name')->get(['id', 'name', 'email']),
            'usedStates' => Chapter::whereNotNull('state')
                ->where('id', '!=', $chapter->id)
                ->pluck('state')
                ->all(),
        ]);
    }

    public function update(Request $request, Chapter $chapter): RedirectResponse
    {
        $data = $this->validated($request, $chapter);

        $before = [$chapter->coordinator_id, $chapter->second_coordinator_id];

        $chapter->update([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['slug'] ?? null, $data['name'], $chapter->id),
            'country' => $data['country'] ?? null,
            'state' => $data['state'] ?? null,
            'city' => $data['city'] ?? null,
            'description' => $data['description'] ?? null,
            'coordinator_id' => $data['coordinator_id'] ?? null,
            'second_coordinator_id' => $data['second_coordinator_id'] ?? null,
            'status' => $data['status'],
        ]);

        $after = [$chapter->coordinator_id, $chapter->second_coordinator_id];

        $removed = array_diff($before, $after);
        $added = array_diff($after, $before);

        $this->notifyCoordinators($chapter, $removed, $added);

        AuditLogger::log('chapter.updated', $chapter, ['name' => $chapter->name]);

        return redirect()->route('admin.chapters.index')->with('success', 'Chapter updated.');
    }

    public function destroy(Chapter $chapter): RedirectResponse
    {
        $chapter->delete();

        AuditLogger::log('chapter.deleted', $chapter, ['name' => $chapter->name]);

        return redirect()->route('admin.chapters.index')->with('success', 'Chapter deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Chapter $chapter = null): array
    {
        $slugRule = Rule::unique('chapters', 'slug');
        if ($chapter) {
            $slugRule->ignore($chapter->id);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'alpha_dash', 'max:180', $slugRule],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:20000'],
            'coordinator_id' => ['nullable', 'exists:users,id'],
            'second_coordinator_id' => ['nullable', 'exists:users,id', 'different:coordinator_id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $data['description'] = RichText::clean($data['description'] ?? null);

        return $data;
    }

    private function notifyCoordinators(Chapter $chapter, array $removed, array $added): void
    {
        foreach (array_filter(array_unique($removed)) as $userId) {
            $user = User::find($userId);

            if ($user && $user->status === 'active') {
                $user->notify(new ChapterCoordinatorRemoved($chapter, auth()->user()));
            }
        }

        foreach (array_filter(array_unique($added)) as $userId) {
            $user = User::find($userId);

            if ($user && $user->status === 'active') {
                $user->notify(new ChapterCoordinatorAssigned($chapter, auth()->user()));
            }
        }
    }

    private function uniqueSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name) ?: 'chapter';
        $candidate = $base;
        $suffix = 2;

        while (Chapter::withTrashed()
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
