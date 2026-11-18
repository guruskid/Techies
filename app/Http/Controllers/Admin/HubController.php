<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hub;
use App\Notifications\HubApplicationReviewed;
use Illuminate\Http\Request;

class HubController extends Controller
{
    public function index(Request $request)
    {
        $q = Hub::with('owner');
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $q->where('name', 'like', "%$s%");
        }
        $hubs = $q->latest()->paginate(20);

        return view('admin.hubs.index', compact('hubs'));
    }

    public function show(Hub $hub)
    {
        $hub->load('owner', 'technologyAreas', 'teamMembers');

        return view('admin.hubs.show', compact('hub'));
    }

    public function approve(Hub $hub)
    {
        $hub->update(['status' => 'approved', 'is_verified' => true, 'is_public' => true]);
        $hub->applications()->latest()->first()?->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $hub->owner?->notify(new HubApplicationReviewed($hub, 'approved'));

        return back()->with('success', 'Hub approved.');
    }

    public function reject(Hub $hub)
    {
        $hub->update(['status' => 'rejected', 'is_verified' => false, 'is_public' => false]);
        $hub->applications()->latest()->first()?->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $hub->owner?->notify(new HubApplicationReviewed($hub, 'rejected'));

        return back()->with('success', 'Hub rejected.');
    }

    public function suspend(Hub $hub)
    {
        $hub->update(['status' => 'suspended', 'is_public' => false]);

        return back()->with('success', 'Hub suspended.');
    }
}
