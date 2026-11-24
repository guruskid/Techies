<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CohortController extends Controller
{
    public function index(): View
    {
        $cohorts = Cohort::withCount('members')
            ->whereIn('status', [Cohort::STATUS_OPEN, Cohort::STATUS_CLOSED])
            ->latest('starts_at')
            ->paginate(12);

        return view('member.cohorts.index', compact('cohorts'));
    }

    public function show(Cohort $cohort): View
    {
        abort_if($cohort->status === Cohort::STATUS_DRAFT, 404);

        $cohort->loadCount('members');
        $joined = $cohort->members()->whereKey(auth()->id())->exists();

        return view('member.cohorts.show', compact('cohort', 'joined'));
    }

    public function join(Cohort $cohort): RedirectResponse
    {
        abort_unless($cohort->isOpen(), 403, 'This cohort is not open for joining.');

        if ($cohort->capacity && $cohort->members()->count() >= $cohort->capacity) {
            return back()->with('error', 'This cohort is full.');
        }

        $cohort->members()->syncWithoutDetaching([
            auth()->id() => ['status' => 'joined', 'joined_at' => now()],
        ]);

        return back()->with('success', 'You have joined '.$cohort->title.'.');
    }

    public function leave(Cohort $cohort): RedirectResponse
    {
        $cohort->members()->detach(auth()->id());

        return back()->with('success', 'You have left '.$cohort->title.'.');
    }
}
