<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemberSpotlight;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpotlightController extends Controller
{
    public function index(): View
    {
        return view('admin.spotlights.index', [
            'spotlights' => MemberSpotlight::with('user')->latest('month')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.spotlights.create', [
            'spotlight' => new MemberSpotlight(['month' => now()->format('Y-m'), 'is_published' => true]),
            'members' => User::where('status', 'active')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $spotlight = MemberSpotlight::create($this->validated($request) + ['created_by' => $request->user()->id]);
        AuditLogger::log('spotlight.created', $spotlight, ['user' => $spotlight->user?->name]);

        return redirect()->route('admin.spotlights.index')->with('success', 'Member spotlight published.');
    }

    public function edit(MemberSpotlight $spotlight): View
    {
        return view('admin.spotlights.edit', [
            'spotlight' => $spotlight,
            'members' => User::where('status', 'active')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, MemberSpotlight $spotlight): RedirectResponse
    {
        $spotlight->update($this->validated($request));
        AuditLogger::log('spotlight.updated', $spotlight, ['user' => $spotlight->user?->name]);

        return redirect()->route('admin.spotlights.index')->with('success', 'Spotlight updated.');
    }

    public function destroy(MemberSpotlight $spotlight): RedirectResponse
    {
        $spotlight->delete();

        return back()->with('success', 'Spotlight deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'month' => ['required', 'date_format:Y-m'],
            'quote' => ['required', 'string', 'max:1000'],
            'achievement' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
