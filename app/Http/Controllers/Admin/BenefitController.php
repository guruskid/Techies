<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemberBenefit;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BenefitController extends Controller
{
    public function index(): View
    {
        return view('admin.benefits.index', [
            'benefits' => MemberBenefit::orderBy('sort_order')->orderBy('title')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.benefits.create', ['benefit' => new MemberBenefit(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $benefit = MemberBenefit::create($this->validated($request));

        AuditLogger::log('benefit.created', $benefit, ['title' => $benefit->title]);

        return redirect()->route('admin.benefits.index')->with('success', 'Member benefit created.');
    }

    public function edit(MemberBenefit $benefit): View
    {
        return view('admin.benefits.edit', compact('benefit'));
    }

    public function update(Request $request, MemberBenefit $benefit): RedirectResponse
    {
        $benefit->update($this->validated($request));

        AuditLogger::log('benefit.updated', $benefit, ['title' => $benefit->title]);

        return redirect()->route('admin.benefits.index')->with('success', 'Member benefit updated.');
    }

    public function destroy(MemberBenefit $benefit): RedirectResponse
    {
        $benefit->delete();

        AuditLogger::log('benefit.deleted', $benefit, ['title' => $benefit->title]);

        return redirect()->route('admin.benefits.index')->with('success', 'Member benefit deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'provider' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'discount_label' => ['nullable', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:100'],
            'url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
