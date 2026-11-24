@extends('layouts.admin')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex items-end justify-between gap-3 mb-6">
    <div>
      <a href="{{ route('admin.dashboard') }}" class="text-sm text-[#FF5C3D] hover:underline">← Admin dashboard</a>
      <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Categories</h1>
      <p class="mt-1 text-sm text-slate-500">Manage categories for events and opportunities.</p>
    </div>
  </div>

  <div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
      <div class="flex items-center justify-between p-5 border-b border-slate-100">
        <h2 class="font-bold text-[#101E33]">Event categories</h2>
        <a href="{{ route('admin.event-categories.create') }}" class="px-3 py-1.5 bg-[#FF5C3D] text-white rounded-lg text-xs font-semibold">New</a>
      </div>
      <table class="w-full text-sm">
        <tbody>
          @forelse(\App\Models\EventCategory::withCount('events')->orderBy('name')->get() as $category)
            <tr class="border-t border-slate-100">
              <td class="p-3 font-medium text-[#101E33]">{{ $category->name }}</td>
              <td class="p-3 text-slate-500">{{ $category->events_count }} events</td>
              <td class="p-3 text-right">
                <a href="{{ route('admin.event-categories.edit', $category) }}" class="text-[#FF5C3D] hover:underline">Edit</a>
                <form method="POST" action="{{ route('admin.event-categories.destroy', $category) }}" class="inline ml-2" onsubmit="return confirm('Delete?')">
                  @csrf @method('DELETE')
                  <button class="text-red-600 hover:underline">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="3" class="p-4 text-center text-slate-500">No event categories.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
      <div class="flex items-center justify-between p-5 border-b border-slate-100">
        <h2 class="font-bold text-[#101E33]">Opportunity categories</h2>
        <a href="{{ route('admin.opportunity-categories.create') }}" class="px-3 py-1.5 bg-[#FF5C3D] text-white rounded-lg text-xs font-semibold">New</a>
      </div>
      <table class="w-full text-sm">
        <tbody>
          @forelse(\App\Models\OpportunityCategory::withCount('opportunities')->orderBy('name')->get() as $category)
            <tr class="border-t border-slate-100">
              <td class="p-3 font-medium text-[#101E33]">{{ $category->name }}</td>
              <td class="p-3 text-slate-500">{{ $category->opportunities_count }} opportunities</td>
              <td class="p-3 text-right">
                <a href="{{ route('admin.opportunity-categories.edit', $category) }}" class="text-[#FF5C3D] hover:underline">Edit</a>
                <form method="POST" action="{{ route('admin.opportunity-categories.destroy', $category) }}" class="inline ml-2" onsubmit="return confirm('Delete?')">
                  @csrf @method('DELETE')
                  <button class="text-red-600 hover:underline">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="3" class="p-4 text-center text-slate-500">No opportunity categories.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
