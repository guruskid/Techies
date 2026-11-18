<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OpportunityCategory;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OpportunityCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.opportunity-categories.index', [
            'categories' => OpportunityCategory::withCount('opportunities')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.opportunity-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:opportunity_categories,name'],
        ]);

        $category = OpportunityCategory::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        AuditLogger::log('opportunity_category.created', $category, ['name' => $category->name]);

        return redirect()->route('admin.opportunity-categories.index')->with('success', 'Opportunity category created.');
    }

    public function edit(OpportunityCategory $opportunityCategory): View
    {
        return view('admin.opportunity-categories.edit', compact('opportunityCategory'));
    }

    public function update(Request $request, OpportunityCategory $opportunityCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('opportunity_categories', 'name')->ignore($opportunityCategory->id)],
        ]);

        $opportunityCategory->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        AuditLogger::log('opportunity_category.updated', $opportunityCategory, ['name' => $opportunityCategory->name]);

        return redirect()->route('admin.opportunity-categories.index')->with('success', 'Opportunity category updated.');
    }

    public function destroy(OpportunityCategory $opportunityCategory): RedirectResponse
    {
        $opportunityCategory->delete();

        AuditLogger::log('opportunity_category.deleted', $opportunityCategory, ['name' => $opportunityCategory->name]);

        return redirect()->route('admin.opportunity-categories.index')->with('success', 'Opportunity category deleted.');
    }
}
