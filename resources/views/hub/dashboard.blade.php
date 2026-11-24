@extends('layouts.app')
@section('content')
@php
  $card = 'bg-white rounded-2xl border border-slate-100 shadow-sm';
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
  $link = 'text-[#FF5C3D] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $row = 'hover:bg-slate-50 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#FF5C3D]';
  $btn = 'inline-flex items-center justify-center px-5 py-2.5 rounded-lg text-sm font-semibold border transition '.$focus;

  $statusBadge = [
    'pending'   => 'bg-amber-50 text-amber-700',
    'approved'  => 'bg-green-50 text-green-700',
    'rejected'  => 'bg-red-50 text-red-700',
    'suspended' => 'bg-slate-200 text-slate-700',
    'published' => 'bg-green-50 text-green-700',
    'draft'     => 'bg-amber-50 text-amber-700',
    'cancelled' => 'bg-red-50 text-red-700',
  ];

  // Hub-entered website: add https:// to bare domains, refuse javascript:, data: and other schemes
  $safeUrl = function ($u) {
    $u = trim((string) $u);
    if ($u === '') return null;
    if (preg_match('#^https?://#i', $u)) return $u;
    if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $u)) return null;
    return 'https://'.ltrim($u, '/');
  };
  $website = $safeUrl($hub->website);
  $place = implode(', ', array_filter([$hub->city, $hub->state, $hub->country]));
  $appStatus = $application?->status;
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 pb-12">

  {{-- Hub header --}}
  <div class="bg-[#101E33] text-white rounded-3xl p-6 sm:p-8 mb-6 flex flex-col md:flex-row md:items-center gap-6">
    @if($hub->logo)
      <img src="{{ $hub->logo }}" alt="" class="w-20 h-20 shrink-0 rounded-2xl object-cover border-4 border-white/10">
    @else
      <div class="w-20 h-20 shrink-0 rounded-2xl bg-white/10 border-4 border-white/10 flex items-center justify-center text-2xl font-bold" aria-hidden="true">{{ strtoupper(substr($hub->name, 0, 1)) }}</div>
    @endif
    <div class="flex-1 min-w-0">
      <h1 class="text-2xl md:text-3xl font-bold">{{ $hub->name }}</h1>
      @if($place)<p class="text-slate-300 mt-1">{{ $place }}</p>@endif
      <div class="mt-3 flex flex-wrap gap-2 text-xs">
        <span class="px-2 py-1 rounded-full bg-white/10">{{ ucfirst($hub->status) }}</span>
        <span class="px-2 py-1 rounded-full {{ $hub->is_verified ? 'bg-green-500/20 text-green-200' : 'bg-yellow-500/20 text-yellow-200' }}">
          {{ $hub->is_verified ? 'Verified hub' : 'Awaiting verification' }}
        </span>
      </div>
    </div>
    {{-- The public page only exists once the hub is approved --}}
    @if($hub->status === 'approved')
      <a href="{{ route('hubs.show', $hub->slug) }}" class="px-5 py-2.5 bg-[#FF5C3D] rounded-lg font-semibold text-center hover:opacity-90 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">View public page</a>
    @endif
  </div>

  {{-- Quick actions --}}
  <div class="flex flex-wrap gap-3 mb-8">
    <a href="{{ route('admin.events.create') }}" class="{{ $btn }} border-[#FF5C3D] bg-[#FF5C3D] text-white hover:opacity-90">Create an event</a>
    <a href="{{ route('admin.opportunities.create') }}" class="{{ $btn }} border-slate-200 bg-white text-slate-800 hover:bg-slate-50">Create an opportunity</a>
    <a href="{{ route('admin.events.index') }}" class="{{ $btn }} border-slate-200 bg-white text-slate-800 hover:bg-slate-50">Manage my events</a>
    <a href="{{ route('admin.speaker-calls.index') }}" class="{{ $btn }} border-slate-200 bg-white text-slate-800 hover:bg-slate-50">Call for speakers</a>
  </div>

  {{-- Stats --}}
  @if(!empty($stats))
    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
      <a href="{{ route('admin.events.index') }}" class="{{ $card }} p-5 flex flex-col-reverse hover:shadow-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
        <dt class="text-sm text-slate-600">Events</dt>
        <dd class="mt-2 text-2xl font-bold text-[#FF5C3D] tabular-nums">{{ number_format($stats['Events'] ?? 0) }}</dd>
      </a>
      <a href="{{ route('admin.events.index') }}" class="{{ $card }} p-5 flex flex-col-reverse hover:shadow-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
        <dt class="text-sm text-slate-600">Upcoming events</dt>
        <dd class="mt-2 text-2xl font-bold text-[#FF5C3D] tabular-nums">{{ number_format($stats['Upcoming events'] ?? 0) }}</dd>
      </a>
      <a href="{{ route('hub.team') }}" class="{{ $card }} p-5 flex flex-col-reverse hover:shadow-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
        <dt class="text-sm text-slate-600">Team members</dt>
        <dd class="mt-2 text-2xl font-bold text-[#FF5C3D] tabular-nums">{{ number_format($stats['Team members'] ?? 0) }}</dd>
      </a>
    </dl>
  @endif

  <div class="grid md:grid-cols-3 gap-6 items-start">

    {{-- Recent events --}}
    <section class="md:col-span-2 {{ $card }} p-6" aria-labelledby="events-h">
      <div class="flex items-center justify-between mb-4">
        <h2 id="events-h" class="text-lg font-bold text-[#101E33]">Recent events</h2>
        <a href="{{ route('events') }}" class="text-sm font-medium {{ $link }}">Browse all events</a>
      </div>
      <ul class="space-y-1">
        @forelse($recentEvents as $event)
          @php
            $date = $event->start_date;
            $published = $event->status === 'published';
            $where = $event->is_online ? 'Online' : ($event->city ?: null);
          @endphp
          <li>
            {{-- Drafts and cancelled events have no public page, so send the owner to edit them --}}
            <a href="{{ $published ? route('events.show', $event->slug) : route('admin.events.edit', $event) }}" class="flex items-center gap-4 p-3 rounded-xl {{ $row }}">
              <div class="w-12 h-12 shrink-0 rounded-xl bg-[#FF5C3D]/10 text-[#FF5C3D] flex flex-col items-center justify-center leading-none" aria-hidden="true">
                @if($date)
                  <span class="text-[10px] font-semibold uppercase">{{ $date->format('M') }}</span>
                  <span class="text-lg font-bold">{{ $date->format('j') }}</span>
                @else
                  <span class="font-bold">{{ strtoupper(substr($event->title, 0, 1)) }}</span>
                @endif
              </div>
              <div class="flex-1 min-w-0">
                <div class="font-medium text-[#101E33] truncate">{{ $event->title }}</div>
                <div class="text-xs text-slate-500">{{ implode(', ', array_filter([$date?->format('M j, Y'), $where])) }}</div>
              </div>
              <span class="shrink-0 text-xs px-2 py-1 rounded-full font-medium {{ $statusBadge[$event->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($event->status) }}</span>
            </a>
          </li>
        @empty
          <li class="text-sm text-slate-500">
            No events have been created for this hub yet.
            <a href="{{ route('admin.events.create') }}" class="{{ $link }}">Create your first event</a>.
          </li>
        @endforelse
      </ul>
    </section>

    {{-- Hub details --}}
    <section class="{{ $card }} p-6" aria-labelledby="details-h">
      <h2 id="details-h" class="text-lg font-bold text-[#101E33] mb-4">Hub details</h2>
      <dl class="space-y-3 text-sm break-words">
        @if($hub->email)
          <div><dt class="text-slate-600">Contact email</dt><dd class="font-medium"><a href="mailto:{{ $hub->email }}" class="{{ $link }}">{{ $hub->email }}</a></dd></div>
        @endif
        @if($hub->phone)
          <div><dt class="text-slate-600">Phone</dt><dd class="font-medium"><a href="tel:{{ preg_replace('/[^\d+]/', '', $hub->phone) }}" class="{{ $link }}">{{ $hub->phone }}</a></dd></div>
        @endif
        @if($website)
          <div><dt class="text-slate-600">Website</dt><dd class="font-medium"><a href="{{ $website }}" target="_blank" rel="noopener noreferrer" class="{{ $link }}">{{ $hub->website }}<span class="sr-only"> (opens in a new tab)</span></a></dd></div>
        @endif
        @foreach(['Organization type' => $hub->organization_type, 'Community size' => $hub->community_size, 'Year established' => $hub->year_established] as $label => $value)
          @if($value)
            <div><dt class="text-slate-600">{{ $label }}</dt><dd class="font-medium text-[#101E33]">{{ $value }}</dd></div>
          @endif
        @endforeach
        @unless($hub->email || $hub->phone || $website || $hub->organization_type || $hub->community_size || $hub->year_established)
          <p class="text-slate-500">No details added yet.</p>
        @endunless
      </dl>
    </section>

  </div>
</div>
@endsection