<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use App\Models\Hub;
use App\Models\HubApplication;
use App\Models\User;
use App\Notifications\HubApplicationSubmitted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class HubController extends Controller
{
    public function show(string $slug): View
    {
        $hub = Hub::where('slug', $slug)->firstOrFail();

        $canPreview = auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super_admin') || $hub->user_id === auth()->id());

        if (! $canPreview && (! $hub->is_public || $hub->status !== 'approved')) {
            abort(404);
        }

        $events = $hub->events()
            ->where('status', 'published')
            ->where('start_date', '>=', now()->toDateString())
            ->orderBy('start_date')
            ->take(6)
            ->get();

        return view('pages.hub-profile', compact('hub', 'events'));
    }

    public function register(Request $request): View
    {
        $applicant = $request->user();
        $existingHub = $applicant?->hub()->first();

        return view('pages.hub-register', [
            'applicant' => $applicant,
            'existingHub' => $existingHub,
            'canRegisterHub' => $applicant !== null
                && $applicant->hasActiveMembership()
                && $existingHub === null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'url'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'contact_position' => ['nullable', 'string', 'max:150'],
            'community_size' => ['nullable', 'string', 'max:50'],
            'year_established' => ['nullable', 'integer'],
            'organization_type' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'linkedin' => ['nullable', 'url'],
            'facebook' => ['nullable', 'url'],
            'instagram' => ['nullable', 'url'],
            'x' => ['nullable', 'url'],
            'youtube' => ['nullable', 'url'],
            'programs_offered' => ['nullable', 'string'],
        ]);
        $owner = $request->user();

        if ($owner->hub()->exists()) {
            return redirect()->route('hub.dashboard')->with('status', 'You already have a hub registered.');
        }

        $hub = DB::transaction(function () use ($data, $owner): Hub {
            $hub = Hub::create(array_merge($data, [
                'user_id' => $owner->id,
                'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(8)),
                'status' => 'pending',
                'is_verified' => false,
                'is_public' => false,
            ]));
            HubApplication::create(['hub_id' => $hub->id, 'status' => 'pending']);
            Role::findOrCreate('hub_owner', 'web');
            $owner->assignRole('hub_owner');

            return $hub;
        });

        User::reviewers()
            ->whereKeyNot($owner->id)
            ->get()
            ->each(fn (User $reviewer) => $reviewer->notify(new HubApplicationSubmitted($hub)));

        return redirect()->route('hub.dashboard')->with('success', 'Hub application submitted and pending review.');
    }
}
