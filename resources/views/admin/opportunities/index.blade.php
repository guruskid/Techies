@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
    <div>
      <a href="{{ route('admin.dashboard') }}" class="text-sm text-[#FF5C3D] hover:underline">← Admin dashboard</a>
      <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Opportunities</h1>
      <p class="mt-1 text-sm text-slate-500">Post jobs, internships and opportunities for the community.</p>
    </div>
    <a href="{{ route('admin.opportunities.create') }}" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition">New opportunity</a>
  </div>

  <form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input name="search" value="{{ request('search') }}" placeholder="Search title or organization" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
    <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
      <option value="">All statuses</option>
      <option value="draft" @selected(request('status') === 'draft')>Draft</option>
      <option value="published" @selected(request('status') === 'published')>Published</option>
    </select>
    <button class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm">Filter</button>
    @if(request()->hasAny(['search', 'status']))
      <a href="{{ route('admin.opportunities.index') }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm">Clear</a>
    @endif
  </form>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-slate-500">
        <tr>
          <th class="text-left p-3">Opportunity</th>
          <th class="text-left p-3">Organization</th>
          <th class="text-left p-3">Status</th>
          <th class="text-left p-3">Visibility</th>
          <th class="text-left p-3"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($opportunities as $opportunity)
          <tr class="border-t border-slate-100">
            <td class="p-3">
              <a href="{{ route('opportunities.show', $opportunity) }}" class="font-medium text-[#101E33] hover:text-[#FF5C3D]">{{ $opportunity->title }}</a>
              <div class="text-xs text-slate-500 mt-0.5">{{ $opportunity->category?->name }}</div>
            </td>
            <td class="p-3 text-slate-600">{{ $opportunity->organization ?? '—' }}</td>
            <td class="p-3">
              <span class="px-2 py-0.5 rounded-full text-xs {{ $opportunity->status === 'published' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($opportunity->status) }}</span>
            </td>
            <td class="p-3">
              @if($opportunity->is_member_only)
                <span class="px-2 py-0.5 rounded-full text-xs bg-[#101E33] text-white">Members only</span>
              @else
                <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">Public</span>
              @endif
            </td>
            <td class="p-3">
              <a href="{{ route('admin.opportunities.edit', $opportunity) }}" class="text-[#FF5C3D] hover:underline">Edit</a>
              <form method="POST" action="{{ route('admin.opportunities.destroy', $opportunity) }}" class="inline ml-2" onsubmit="return confirm('Delete this opportunity?')">
                @csrf
                @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="p-6 text-center text-slate-500">No opportunities yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $opportunities->links() }}</div>
</div>
@endsection
