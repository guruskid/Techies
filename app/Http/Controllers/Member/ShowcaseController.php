<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ShowcaseProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShowcaseController extends Controller
{
    public function index(): View
    {
        $projects = ShowcaseProject::with('user.profile')
            ->where('status', ShowcaseProject::STATUS_APPROVED)
            ->latest()
            ->paginate(12);

        $mine = ShowcaseProject::where('user_id', auth()->id())->latest()->get();

        return view('member.showcase.index', compact('projects', 'mine'));
    }

    public function create(): View
    {
        return view('member.showcase.create', ['project' => new ShowcaseProject(['looking_for_collaborators' => false])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        ShowcaseProject::create(array_merge($data, [
            'user_id' => $request->user()->id,
            'slug' => $this->slug($data['title']),
            'status' => ShowcaseProject::STATUS_PENDING,
        ]));

        return redirect()->route('projects.index')->with('success', 'Project submitted for review.');
    }

    public function edit(ShowcaseProject $project): View
    {
        abort_unless($project->user_id === auth()->id(), 403);

        return view('member.showcase.edit', compact('project'));
    }

    public function update(Request $request, ShowcaseProject $project): RedirectResponse
    {
        abort_unless($project->user_id === auth()->id(), 403);
        $data = $this->validated($request);

        $project->update(array_merge($data, [
            'slug' => $this->slug($data['title'], $project->id),
            'status' => ShowcaseProject::STATUS_PENDING,
        ]));

        return redirect()->route('projects.index')->with('success', 'Project updated and sent for review.');
    }

    public function destroy(ShowcaseProject $project): RedirectResponse
    {
        abort_unless($project->user_id === auth()->id(), 403);
        $project->delete();

        return back()->with('success', 'Project removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'summary' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'url' => ['nullable', 'url', 'max:2048'],
            'repo_url' => ['nullable', 'url', 'max:2048'],
            'image' => ['nullable', 'url', 'max:2048'],
            'stack_text' => ['nullable', 'string', 'max:500'],
        ]);

        $data['stack'] = collect(explode(',', (string) ($data['stack_text'] ?? '')))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
        $data['looking_for_collaborators'] = $request->boolean('looking_for_collaborators');

        unset($data['stack_text']);

        return $data;
    }

    private function slug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'project';
        $candidate = $base;
        $suffix = 2;

        while (ShowcaseProject::where('slug', $candidate)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
