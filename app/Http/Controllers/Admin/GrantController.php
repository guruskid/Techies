<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grant;
use App\Models\GrantApplication;
use App\Notifications\GrantApplicationUpdated;
use App\Support\AuditLogger;
use App\Support\RichText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GrantController extends Controller
{
    public function index(): View
    {
        return view('admin.grants.index', [
            'grants' => Grant::withCount('applications')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.grants.create', ['grant' => new Grant(['status' => Grant::STATUS_DRAFT])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $grant = Grant::create(array_merge($data, ['slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(5)), 'created_by' => $request->user()->id]));
        AuditLogger::log('grant.created', $grant, ['title' => $grant->title]);

        return redirect()->route('admin.grants.index')->with('success', 'Grant created.');
    }

    public function edit(Grant $grant): View
    {
        return view('admin.grants.edit', compact('grant'));
    }

    public function update(Request $request, Grant $grant): RedirectResponse
    {
        $grant->update($this->validated($request));
        AuditLogger::log('grant.updated', $grant, ['title' => $grant->title]);

        return redirect()->route('admin.grants.index')->with('success', 'Grant updated.');
    }

    public function applications(Grant $grant): View
    {
        return view('admin.grants.applications', [
            'grant' => $grant,
            'applications' => $grant->applications()->with('user')->latest()->get(),
        ]);
    }

    public function review(Request $request, Grant $grant, GrantApplication $application): RedirectResponse
    {
        abort_unless($application->grant_id === $grant->id, 404);

        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $application->update([
            'status' => $data['status'],
            'review_notes' => $data['review_notes'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $application->user?->notify(new GrantApplicationUpdated($grant, $application));

        return back()->with('success', 'Application '.$data['status'].'.');
    }

    public function destroy(Grant $grant): RedirectResponse
    {
        $grant->delete();
        AuditLogger::log('grant.deleted', $grant, ['title' => $grant->title]);

        return back()->with('success', 'Grant deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:10000'],
            'amount' => ['nullable', 'integer', 'min:0'],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date', 'after_or_equal:opens_at'],
            'status' => ['required', Rule::in([Grant::STATUS_DRAFT, Grant::STATUS_OPEN, Grant::STATUS_CLOSED])],
        ]);

        $data['description'] = RichText::clean($data['description']);

        return $data;
    }
}
