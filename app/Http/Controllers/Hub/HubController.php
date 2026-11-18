<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use App\Models\Hub;

class HubController extends Controller
{
    public function show($slug)
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
}
