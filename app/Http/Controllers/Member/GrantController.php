<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Grant;
use App\Models\GrantApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GrantController extends Controller
{
    public function index(): View
    {
        $grants = Grant::whereIn('status', [Grant::STATUS_OPEN, Grant::STATUS_CLOSED])->latest('closes_at')->paginate(12);

        return view('member.grants.index', compact('grants'));
    }

    public function show(Grant $grant): View
    {
        abort_if($grant->status === Grant::STATUS_DRAFT, 404);
        $application = GrantApplication::where('grant_id', $grant->id)->where('user_id', auth()->id())->first();

        return view('member.grants.show', compact('grant', 'application'));
    }

    public function apply(Request $request, Grant $grant): RedirectResponse
    {
        abort_unless($grant->isOpen(), 403, 'This grant is not open.');

        $data = $request->validate([
            'project_title' => ['required', 'string', 'max:200'],
            'summary' => ['required', 'string', 'max:2000'],
            'budget_breakdown' => ['nullable', 'string', 'max:3000'],
        ]);

        GrantApplication::firstOrCreate(
            ['grant_id' => $grant->id, 'user_id' => $request->user()->id],
            $data + ['status' => 'pending']
        );

        return back()->with('success', 'Your grant application has been submitted.');
    }
}
