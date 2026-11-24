<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Chapter;
use App\Support\AuditLogger;
use App\Support\RichText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $query = Announcement::with('author', 'chapter');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_published', $request->string('status')->toString() === 'published');
        }

        return view('admin.announcements.index', [
            'announcements' => $query->latest()->paginate(20)->withQueryString(),
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.announcements.create', [
            'announcement' => new Announcement,
            'chapters' => $this->chapters(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $announcement = Announcement::create([
            ...$data,
            'author_id' => auth()->id(),
            'is_published' => $request->boolean('is_published'),
            'published_at' => $request->boolean('is_published') ? now() : null,
        ]);

        AuditLogger::log('announcement.created', $announcement, ['title' => $announcement->title]);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement saved.');
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.edit', [
            'announcement' => $announcement,
            'chapters' => $this->chapters(),
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $data = $this->validated($request);
        $publishing = $request->boolean('is_published');

        $announcement->update([
            ...$data,
            'is_published' => $publishing,
            'published_at' => $publishing
                ? ($announcement->published_at ?? now())
                : null,
        ]);

        AuditLogger::log('announcement.updated', $announcement, ['title' => $announcement->title]);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $title = $announcement->title;
        $announcement->delete();

        AuditLogger::log('announcement.deleted', null, ['title' => $title]);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:50000'],
            'chapter_id' => ['nullable', Rule::exists('chapters', 'id')],
            'is_published' => ['nullable', 'boolean'],
        ]);

        unset($data['is_published']);

        $data['body'] = RichText::clean($data['body']);

        return $data;
    }

    private function chapters()
    {
        return Chapter::orderBy('name')->get(['id', 'name']);
    }
}
