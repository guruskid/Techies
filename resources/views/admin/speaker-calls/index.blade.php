@extends('layouts.admin')
@section('content')
@php
  $user = auth()->user();
  $isStaff = $user->isAdmin() || $user->isSuperAdmin();
  $homeUrl = $isStaff ? route('admin.dashboard') : route('dashboard');
  $homeLabel = $isStaff ? 'Back to admin dashboard' : 'Back to my dashboard';
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-1';
  $badges = [
    'draft' => 'bg-slate-100 text-slate-600',
    'open' => 'bg-green-50 text-green-700',
    'closed' => 'bg-amber-50 text-amber-700',
  ];
@endphp

<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
    <div>
      <a href="{{ $homeUrl }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }} rounded">{{ $homeLabel }}</a>
      <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Calls for speakers</h1>
      <p class="mt-1 text-sm text-slate-500">Open a call, collect proposals and email speakers when you approve or reject them.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('speaker-calls.index') }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 transition {{ $focus }}">Public calls</a>
      <a href="{{ route('admin.speaker-calls.create') }}" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition {{ $focus }}">New call</a>
    </div>
  </div>

  <form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input name="search" value="{{ request('search') }}" placeholder="Search title or description" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
    <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
      <option value="">All statuses</option>
      @foreach(\App\Models\SpeakerCall::statuses() as $value => $label)
        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
      @endforeach
    </select>
    <button class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm">Filter</button>
    @if(request()->hasAny(['search', 'status']))
      <a href="{{ route('admin.speaker-calls.index') }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm">Clear</a>
    @endif
  </form>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-slate-500">
        <tr>
          <th class="text-left p-3">Call</th>
          <th class="text-left p-3">Linked to</th>
          <th class="text-left p-3">Status</th>
          <th class="text-left p-3">Closes</th>
          <th class="text-left p-3">Submissions</th>
          <th class="text-left p-3"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($speakerCalls as $call)
          <tr class="border-t border-slate-100">
            <td class="p-3">
              <span class="font-medium text-[#101E33]">{{ $call->title }}</span>
              <div class="text-xs text-slate-500 mt-0.5">{{ count($call->topicList()) }} {{ \Illuminate\Support\Str::plural('topic', count($call->topicList())) }}</div>
            </td>
            <td class="p-3 text-slate-600">
              {{ $call->event?->title ?? $call->hub?->name ?? $call->chapter?->name ?? 'Platform-wide' }}
            </td>
            <td class="p-3">
              <span class="px-2 py-0.5 rounded-full text-xs {{ $badges[$call->status] ?? 'bg-slate-100 text-slate-600' }}">{{ \App\Models\SpeakerCall::statuses()[$call->status] ?? ucfirst($call->status) }}</span>
            </td>
            <td class="p-3 text-slate-600">{{ $call->closes_at?->format('M j, Y') ?? 'No deadline' }}</td>
            <td class="p-3"><span class="font-medium text-[#101E33]">{{ $call->submissions_count }}</span></td>
            <td class="p-3 whitespace-nowrap">
              <a href="{{ route('admin.speaker-calls.submissions', $call) }}" class="text-[#FF5C3D] hover:underline">Review</a>
              <a href="{{ route('admin.speaker-calls.edit', $call) }}" class="text-[#FF5C3D] hover:underline ml-2">Edit</a>
              @if($call->status !== 'draft')
                <a href="{{ route('speaker-calls.show', $call->slug) }}" class="text-slate-600 hover:underline ml-2">View</a>
              @endif
              <form method="POST" action="{{ route('admin.speaker-calls.destroy', $call) }}" class="inline ml-2" onsubmit="return confirm('Delete this call and its submissions?')">
                @csrf
                @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="p-6 text-center text-slate-500">No calls for speakers yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $speakerCalls->links() }}</div>
</div>
@endsection