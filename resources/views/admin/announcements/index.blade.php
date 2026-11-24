@extends('layouts.admin')
@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $field = 'border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
    <div>
      <a href="{{ route('admin.manage') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }}">← Management center</a>
      <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Announcements</h1>
      <p class="mt-1 text-sm text-slate-500">Publish community updates for the news page and member dashboard.</p>
    </div>
    <a href="{{ route('admin.announcements.create') }}" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition {{ $focus }}">New announcement</a>
  </div>

  <form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input name="search" value="{{ $search }}" placeholder="Search title or body" class="{{ $field }} w-64 max-w-full">
    <select name="status" class="{{ $field }}">
      <option value="">All statuses</option>
      <option value="published" @selected($status === 'published')>Published</option>
      <option value="draft" @selected($status === 'draft')>Draft</option>
    </select>
    <button class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm {{ $focus }}">Filter</button>
    @if($search !== '' || $status !== '')
      <a href="{{ route('admin.announcements.index') }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm {{ $focus }}">Clear</a>
    @endif
  </form>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
    <table class="w-full min-w-[720px] text-sm">
      <thead class="bg-slate-50 text-slate-500">
        <tr>
          <th class="text-left p-3 font-medium">Announcement</th>
          <th class="text-left p-3 font-medium">Chapter</th>
          <th class="text-left p-3 font-medium">Status</th>
          <th class="text-left p-3 font-medium">Published</th>
          <th class="text-left p-3 font-medium"><span class="sr-only">Actions</span></th>
        </tr>
      </thead>
      <tbody>
        @forelse($announcements as $announcement)
          <tr class="border-t border-slate-100 align-top">
            <td class="p-3">
              <div class="font-medium text-[#101E33]">{{ $announcement->title }}</div>
              <div class="mt-0.5 text-xs text-slate-500">{{ \Illuminate\Support\Str::limit(strip_tags((string) $announcement->body), 110) }}</div>
              <div class="mt-1 text-[11px] text-slate-400">By {{ $announcement->author?->name ?? 'Deleted user' }}</div>
            </td>
            <td class="p-3 text-slate-600">{{ $announcement->chapter?->name ?? 'All members' }}</td>
            <td class="p-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium {{ $announcement->is_published ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                {{ $announcement->is_published ? 'Published' : 'Draft' }}
              </span>
            </td>
            <td class="p-3 whitespace-nowrap text-slate-600">{{ $announcement->published_at?->format('M j, Y') ?? '—' }}</td>
            <td class="p-3">
              <div class="flex items-center gap-3">
                <a href="{{ route('admin.announcements.edit', $announcement) }}" class="text-[#FF5C3D] hover:underline {{ $focus }}">Edit</a>
                <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete this announcement?')">
                  @csrf
                  @method('DELETE')
                  <button class="text-red-600 hover:underline {{ $focus }}">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="p-8 text-center text-slate-500">No announcements yet. Create one to tell members what is happening.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <nav class="mt-4" aria-label="Pagination">{{ $announcements->links() }}</nav>
</div>
@endsection
