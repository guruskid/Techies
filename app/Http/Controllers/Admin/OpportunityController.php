<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use App\Models\OpportunityApplication;
use App\Models\OpportunityCategory;
use App\Models\User;
use App\Notifications\ApplicationStatusUpdated;
use App\Notifications\OpportunityPublished;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OpportunityController extends Controller
{
    public function index(Request $request): View
    {
        $query = Opportunity::with('category');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('organization', 'like', "%{$search}%");
            });
        }

        return view('admin.opportunities.index', [
            'opportunities' => $query->latest()->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.opportunities.create', [
            'categories' => OpportunityCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $wasPublished = false;

        $opportunity = Opportunity::create([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['slug'] ?? null, $data['title']),
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'organization' => $data['organization'] ?? null,
            'location' => $data['location'] ?? null,
            'is_remote' => (bool) ($data['is_remote'] ?? false),
            'is_member_only' => (bool) ($data['is_member_only'] ?? false),
            'application_link' => $data['application_link'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'eligibility' => $data['eligibility'] ?? null,
            'cover_image' => $data['cover_image'] ?? null,
            'status' => $data['status'],
            'submitted_by' => auth()->id(),
        ]);

        if ($opportunity->status === 'published') {
            $wasPublished = true;
        }

        AuditLogger::log('opportunity.created', $opportunity, ['title' => $opportunity->title]);

        if ($wasPublished) {
            $this->notifyMembers($opportunity);
        }

        return redirect()->route('admin.opportunities.index')->with('success', 'Opportunity created.');
    }

    public function edit(Opportunity $opportunity): View
    {
        return view('admin.opportunities.edit', [
            'opportunity' => $opportunity,
            'categories' => OpportunityCategory::orderBy('name')->get(),
            'applications' => $opportunity->applications()->with('user')->latest()->get(),
        ]);
    }

    public function update(Request $request, Opportunity $opportunity): RedirectResponse
    {
        $data = $this->validated($request);
        $wasPublished = $opportunity->status !== 'published' && $data['status'] === 'published';

        $opportunity->update([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['slug'] ?? null, $data['title'], $opportunity->id),
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'organization' => $data['organization'] ?? null,
            'location' => $data['location'] ?? null,
            'is_remote' => (bool) ($data['is_remote'] ?? false),
            'is_member_only' => (bool) ($data['is_member_only'] ?? false),
            'application_link' => $data['application_link'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'eligibility' => $data['eligibility'] ?? null,
            'cover_image' => $data['cover_image'] ?? null,
            'status' => $data['status'],
        ]);

        AuditLogger::log('opportunity.updated', $opportunity, ['title' => $opportunity->title]);

        if ($wasPublished) {
            $this->notifyMembers($opportunity);
        }

        return redirect()->route('admin.opportunities.index')->with('success', 'Opportunity updated.');
    }

    public function updateApplication(Request $request, Opportunity $opportunity, OpportunityApplication $application): RedirectResponse
    {
        abort_unless($application->opportunity_id === $opportunity->id, 404);

        $data = $request->validate([
            'status' => ['required', Rule::in(OpportunityApplication::statuses())],
        ]);

        $previous = $application->status;
        $application->update(['status' => $data['status']]);

        AuditLogger::log('opportunity.application.updated', $application, [
            'opportunity' => $opportunity->title,
            'from' => $previous,
            'to' => $data['status'],
        ]);

        if ($previous !== $data['status']) {
            $application->user?->notify(new ApplicationStatusUpdated($opportunity, $data['status']));
        }

        return back()->with('success', 'Application updated. The applicant has been emailed the new status.');
    }

    /**
     * Download the applicant list for an opportunity as a CSV file.
     */
    public function exportApplications(Opportunity $opportunity): StreamedResponse
    {
        $applications = $opportunity->applications()->with('user.profile')->latest('applied_at')->get();

        AuditLogger::log('opportunity.applications.exported', $opportunity, ['title' => $opportunity->title]);

        return response()->streamDownload(function () use ($applications): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Phone', 'Status', 'Applied at', 'Cover letter'], ',', '"', '');

            foreach ($applications as $application) {
                fputcsv($handle, [
                    $application->user?->name,
                    $application->user?->email,
                    $application->user?->profile?->phone,
                    $application->status,
                    $application->applied_at?->toDateTimeString(),
                    $application->cover_letter,
                ], ',', '"', '');
            }

            fclose($handle);
        }, 'opportunity-'.$opportunity->slug.'-applicants.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function destroy(Opportunity $opportunity): RedirectResponse
    {        $opportunity->delete();

        AuditLogger::log('opportunity.deleted', $opportunity, ['title' => $opportunity->title]);

        return redirect()->route('admin.opportunities.index')->with('success', 'Opportunity deleted.');
    }

    private function notifyMembers(Opportunity $opportunity): void
    {
        $members = User::where('status', 'active')->get();

        foreach ($members as $member) {
            $member->notify(new OpportunityPublished($opportunity));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'alpha_dash', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['nullable', 'exists:opportunity_categories,id'],
            'organization' => ['nullable', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:200'],
            'is_remote' => ['nullable', 'boolean'],
            'is_member_only' => ['nullable', 'boolean'],
            'application_link' => ['nullable', 'url', 'max:2048'],
            'deadline' => ['nullable', 'date'],
            'eligibility' => ['nullable', 'string', 'max:2000'],
            'cover_image' => ['nullable', 'url', 'max:2048'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);
    }

    private function uniqueSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name) ?: 'opportunity';
        $candidate = $base;
        $suffix = 2;

        while (Opportunity::withTrashed()
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
