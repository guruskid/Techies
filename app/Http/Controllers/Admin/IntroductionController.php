<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntroRequest;
use App\Notifications\IntroductionUpdated;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntroductionController extends Controller
{
    public function index(): View
    {
        return view('admin.introductions.index', [
            'requests' => IntroRequest::with('requester', 'target', 'handler')->latest()->paginate(30),
        ]);
    }

    public function update(Request $request, IntroRequest $introRequest): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:completed,declined'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $introRequest->update([
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'] ?? null,
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ]);

        AuditLogger::log('introduction.'.$data['status'], $introRequest, ['requester' => $introRequest->requester?->name]);

        $introRequest->requester?->notify(new IntroductionUpdated($introRequest));
        $introRequest->target?->notify(new IntroductionUpdated($introRequest));

        return back()->with('success', 'Introduction request updated and the members have been emailed.');
    }
}
