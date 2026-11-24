<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShowcaseProject;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowcaseController extends Controller
{
    public function index(): View
    {
        return view('admin.showcase.index', [
            'projects' => ShowcaseProject::with('user')->latest()->paginate(30),
        ]);
    }

    public function moderate(Request $request, ShowcaseProject $project): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'featured' => ['nullable', 'boolean'],
        ]);

        $project->update([
            'status' => $data['decision'],
            'is_featured' => $request->boolean('featured'),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        AuditLogger::log('showcase.'.$data['decision'], $project, ['title' => $project->title]);

        return back()->with('success', 'Project '.$data['decision'].'.');
    }

    public function destroy(ShowcaseProject $project): RedirectResponse
    {
        $project->delete();
        AuditLogger::log('showcase.deleted', $project, ['title' => $project->title]);

        return back()->with('success', 'Project deleted.');
    }
}
