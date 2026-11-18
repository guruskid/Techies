<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventCategory;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.event-categories.index', [
            'categories' => EventCategory::withCount('events')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.event-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:event_categories,name'],
        ]);

        $category = EventCategory::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        AuditLogger::log('event_category.created', $category, ['name' => $category->name]);

        return redirect()->route('admin.event-categories.index')->with('success', 'Event category created.');
    }

    public function edit(EventCategory $eventCategory): View
    {
        return view('admin.event-categories.edit', compact('eventCategory'));
    }

    public function update(Request $request, EventCategory $eventCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('event_categories', 'name')->ignore($eventCategory->id)],
        ]);

        $eventCategory->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        AuditLogger::log('event_category.updated', $eventCategory, ['name' => $eventCategory->name]);

        return redirect()->route('admin.event-categories.index')->with('success', 'Event category updated.');
    }

    public function destroy(EventCategory $eventCategory): RedirectResponse
    {
        $eventCategory->delete();

        AuditLogger::log('event_category.deleted', $eventCategory, ['name' => $eventCategory->name]);

        return redirect()->route('admin.event-categories.index')->with('success', 'Event category deleted.');
    }
}
