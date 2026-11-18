<?php

namespace App\Http\Controllers\Opportunity;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use App\Models\OpportunityApplication;
use App\Notifications\ApplicationSubmitted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpportunityController extends Controller
{
    public function show($slug): View
    {
        $opportunity = Opportunity::where('slug', $slug)->where('status', 'published')->with('category', 'hub')->firstOrFail();

        if ($opportunity->is_member_only && ! auth()->check()) {
            abort(403, 'This opportunity is available to MWTT members only.');
        }

        $hasApplied = auth()->check()
            ? $opportunity->applications()->where('user_id', auth()->id())->exists()
            : false;

        return view('pages.opportunity-detail', compact('opportunity', 'hasApplied'));
    }

    public function apply(Request $request, Opportunity $opportunity): RedirectResponse
    {
        abort_unless($opportunity->status === 'published', 404);

        $data = $request->validate([
            'cover_letter' => ['nullable', 'string', 'max:3000'],
        ]);

        $application = OpportunityApplication::firstOrCreate(
            ['opportunity_id' => $opportunity->id, 'user_id' => auth()->id()],
            ['cover_letter' => $data['cover_letter'] ?? null, 'status' => 'applied', 'applied_at' => now()]
        );

        if (! $application->wasRecentlyCreated && isset($data['cover_letter'])) {
            $application->update(['cover_letter' => $data['cover_letter']]);
        }

        if ($application->wasRecentlyCreated) {
            auth()->user()->notify(new ApplicationSubmitted($opportunity));
        }

        return back()->with('success', $application->wasRecentlyCreated
            ? 'Your application has been submitted.'
            : 'You have already applied for this opportunity.');
    }
}
