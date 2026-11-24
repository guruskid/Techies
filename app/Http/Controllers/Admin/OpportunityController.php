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
use App\Support\RichText;
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

        $user = $request->user();
        if (! $user->isAdmin() && ! $user->isSuperAdmin()) {
            $hubId = $user->hub?->id;

            $query->where(function ($builder) use ($user, $hubId): void {
                $builder->where('submitted_by', $user->id);

                if ($hubId) {
                    $builder->orWhere('hub_id', $hubId);
                }
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

        $user = auth()->user();
        if (! $user->isAdmin() && ! $user->isSuperAdmin()) {
            $data['status'] = 'draft';
            if ($user->hasRole('hub_owner')) {
                $data['hub_id'] = $user->hub?->id;
            }
        }

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
            'hub_id' => $data['hub_id'] ?? null,
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
        $this->ensureCanManage($opportunity);

        return view('admin.opportunities.edit', [
            'opportunity' => $opportunity,
            'categories' => OpportunityCategory::orderBy('name')->get(),
            'applications' => $opportunity->applications()->with('user')->latest()->get(),
        ]);
    }

    public function update(Request $request, Opportunity $opportunity): RedirectResponse
    {
        $this->ensureCanManage($opportunity);

        $data = $this->validated($request);

        $user = $request->user();
        if (! $user->isAdmin() && ! $user->isSuperAdmin()) {
            // Publishing stays an admin decision; keep live posts live on edit.
            $data['status'] = $opportunity->status === 'published' ? 'published' : 'draft';
        }

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
        $this->ensureCanManage($opportunity);
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
        $this->ensureCanManage($opportunity);

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
    {
        $this->ensureCanManage($opportunity);

        $opportunity->delete();

        AuditLogger::log('opportunity.deleted', $opportunity, ['title' => $opportunity->title]);

        return redirect()->route('admin.opportunities.index')->with('success', 'Opportunity deleted.');
    }

    /**
     * Only staff, the submitter and the owning hub may work with a post.
     */
    private function ensureCanManage(Opportunity $opportunity): void
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return;
        }

        if ((int) $opportunity->submitted_by === (int) $user->id) {
            return;
        }

        if ($opportunity->hub_id && (int) $opportunity->hub_id === (int) $user->hub?->id) {
            return;
        }

        abort(403);
    }

    private function notifyMembers(Opportunity $opportunity): void
    {
        $state = $opportunity->hub?->state;

        $members = User::where('status', 'active');

        if ($state) {
            $members->whereHas('profile', fn ($query) => $query->where('state', $state));
        }

        foreach ($members->get() as $member) {
            $member->notify(new OpportunityPublished($opportunity));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'alpha_dash', 'max:180'],
            'description' => ['nullable', 'string', 'max:30000'],
            'category_id' => ['nullable', 'exists:opportunity_categories,id'],
            'organization' => ['nullable', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:200'],
            'is_remote' => ['nullable', 'boolean'],
            'is_member_only' => ['nullable', 'boolean'],
            'application_link' => ['nullable', 'url', 'max:2048'],
            'deadline' => ['nullable', 'date'],
            'eligibility' => ['nullable', 'string', 'max:20000'],
            'cover_image' => ['nullable', 'url', 'max:2048'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);

        foreach (['description', 'eligibility'] as $field) {
            $data[$field] = RichText::clean($data[$field] ?? null);
        }

        return $data;
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
