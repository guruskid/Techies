<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use App\Support\AuditLogger;
use App\Support\RichText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CohortController extends Controller
{
    public function index(): View
    {
        return view('admin.cohorts.index', [
            'cohorts' => Cohort::withCount('members')->latest('starts_at')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.cohorts.create', ['cohort' => new Cohort(['status' => Cohort::STATUS_DRAFT])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $cohort = Cohort::create(array_merge($data, [
            'slug' => $this->slug($data['title']),
            'created_by' => $request->user()->id,
        ]));

        AuditLogger::log('cohort.created', $cohort, ['title' => $cohort->title]);

        return redirect()->route('admin.cohorts.index')->with('success', 'Cohort created.');
    }

    public function edit(Cohort $cohort): View
    {
        return view('admin.cohorts.edit', compact('cohort'));
    }

    public function update(Request $request, Cohort $cohort): RedirectResponse
    {
        $data = $this->validated($request);
        $cohort->update(array_merge($data, ['slug' => $this->slug($data['title'], $cohort->id)]));

        AuditLogger::log('cohort.updated', $cohort, ['title' => $cohort->title]);

        return redirect()->route('admin.cohorts.index')->with('success', 'Cohort updated.');
    }

    public function destroy(Cohort $cohort): RedirectResponse
    {
        $cohort->delete();

        AuditLogger::log('cohort.deleted', $cohort, ['title' => $cohort->title]);

        return redirect()->route('admin.cohorts.index')->with('success', 'Cohort deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:10000'],
            'track' => ['nullable', 'string', 'max:120'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', Rule::in([Cohort::STATUS_DRAFT, Cohort::STATUS_OPEN, Cohort::STATUS_CLOSED])],
        ]);

        $data['description'] = RichText::clean($data['description'] ?? null);

        return $data;
    }

    private function slug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'cohort';
        $candidate = $base;
        $suffix = 2;

        while (Cohort::where('slug', $candidate)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
