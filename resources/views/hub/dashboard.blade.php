@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

  <!-- Hub header -->
  <div class="bg-[#101E33] text-white rounded-3xl p-8 mb-8 flex flex-col md:flex-row items-start md:items-center gap-6">
    @if($hub->logo)
      <img src="{{ $hub->logo }}" alt="{{ $hub->name }}" class="w-20 h-20 rounded-2xl object-cover border-4 border-white/10">
    @else
      <div class="w-20 h-20 rounded-2xl bg-white/10 border-4 border-white/10 flex items-center justify-center text-2xl font-bold">{{ strtoupper(substr($hub->name, 0, 1)) }}</div>
    @endif
    <div class="flex-1">
      <h1 class="text-2xl md:text-3xl font-bold">{{ $hub->name }}</h1>
      <p class="text-slate-300 mt-1">{{ $hub->city }}{{ $hub->state ? ', '.$hub->state : '' }}{{ $hub->country ? ', '.$hub->country : '' }}</p>
      <div class="mt-3 flex flex-wrap gap-2 text-xs">
        <span class="px-2 py-1 rounded-full bg-white/10">{{ ucfirst($hub->status) }}</span>
        <span class="px-2 py-1 rounded-full {{ $hub->is_verified ? 'bg-green-500/20 text-green-200' : 'bg-yellow-500/20 text-yellow-200' }}">
          {{ $hub->is_verified ? 'Verified hub' : 'Awaiting verification' }}
        </span>
      </div>
    </div>
    <a href="{{ route('hubs.show', $hub->slug) }}" class="px-5 py-2.5 bg-[#FF5C3D] rounded-lg font-semibold hover:opacity-90 transition">View Public Page</a>
  </div>

  @if($application && $application->status === 'pending')
    <div class="mb-6 p-4 rounded-xl bg-yellow-50 border border-yellow-100 text-yellow-800 text-sm">
      Your hub application is pending review. You can update your details while you wait.
    </div>
  @elseif($application && $application->status === 'rejected')
    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm">
      Your hub application was not approved.
      @if($application->review_notes) <span class="block mt-1">Reviewer notes: {{ $application->review_notes }}</span> @endif
    </div>
  @endif

  <!-- Stats -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    @foreach($stats as $label => $value)
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="text-sm text-slate-500">{{ $label }}</div>
        <div class="mt-2 text-2xl font-bold text-[#FF5C3D]">{{ $value }}</div>
      </div>
    @endforeach
  </div>

  <div class="grid md:grid-cols-3 gap-6">
    <!-- Recent events -->
    <div class="md:col-span-2 bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-[#101E33]">Recent Events</h2>
        <a href="{{ route('events') }}" class="text-sm text-[#FF5C3D] hover:underline">Browse all events</a>
      </div>
      <div class="space-y-3">
        @forelse($recentEvents as $event)
          <a href="{{ route('events.show', $event->slug) }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-50 transition">
            <div class="w-12 h-12 rounded-xl bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center font-bold">{{ strtoupper(substr($event->title, 0, 1)) }}</div>
            <div class="flex-1 min-w-0">
              <div class="font-medium text-[#101E33] truncate">{{ $event->title }}</div>
              <div class="text-xs text-slate-500">
                {{ $event->start_date?->format('M j, Y') }}
                @if($event->is_online) · Online @elseif($event->city) · {{ $event->city }} @endif
              </div>
            </div>
            <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-600">{{ ucfirst($event->status) }}</span>
          </a>
        @empty
          <p class="text-sm text-slate-500">No events have been published for this hub yet.</p>
        @endforelse
      </div>
    </div>

    <!-- Hub details -->
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h2 class="text-lg font-bold text-[#101E33] mb-3">Hub Details</h2>
      <dl class="space-y-3 text-sm">
        <div><dt class="text-slate-500">Contact email</dt><dd class="font-medium text-[#101E33]">{{ $hub->email ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">Phone</dt><dd class="font-medium text-[#101E33]">{{ $hub->phone ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">Website</dt><dd class="font-medium text-[#101E33]">
          @if($hub->website)
            <a href="{{ $hub->website }}" target="_blank" rel="noopener" class="text-[#FF5C3D] hover:underline">{{ $hub->website }}</a>
          @else
            —
          @endif
        </dd></div>
        <div><dt class="text-slate-500">Community size</dt><dd class="font-medium text-[#101E33]">{{ $hub->community_size ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">Year established</dt><dd class="font-medium text-[#101E33]">{{ $hub->year_established ?? '—' }}</dd></div>
      </dl>
    </div>
  </div>
</div>
@endsection
