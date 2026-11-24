@extends('layouts.app')
@section('content')
@php
  $badge = [
    'pending'   => 'bg-amber-50 text-amber-700',
    'approved'  => 'bg-green-50 text-green-700',
    'rejected'  => 'bg-red-50 text-red-700',
    'suspended' => 'bg-slate-200 text-slate-700',
  ];
  $card = 'bg-white rounded-2xl p-6 border border-slate-100 shadow-sm';
  $h2 = 'text-lg font-bold text-[#101E33] mb-4';
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
  $link = 'text-[#FF5C3D] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $btn = 'inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-semibold border transition '.$focus;
  $btnApprove = $btn.' border-green-600 bg-green-600 text-white hover:bg-green-700';
  $btnRestore = $btn.' border-green-600 text-green-700 bg-white hover:bg-green-50';
  $btnReject  = $btn.' border-red-300 text-red-700 bg-white hover:bg-red-50';
  $btnSuspend = $btn.' border-amber-400 text-amber-800 bg-white hover:bg-amber-50';

  // Hub-supplied URLs: add https:// to bare domains, refuse javascript:, data: and other schemes
  $safeUrl = function ($u) {
    $u = trim((string) $u);
    if ($u === '') return null;
    if (preg_match('#^https?://#i', $u)) return $u;
    if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $u)) return null;
    return 'https://'.ltrim($u, '/');
  };

  $place = implode(', ', array_filter([$hub->city, $hub->state, $hub->country]));
  $website = $safeUrl($hub->website);
  $socials = collect([
    'LinkedIn' => $hub->linkedin, 'Facebook' => $hub->facebook, 'Instagram' => $hub->instagram,
    'X' => $hub->x, 'YouTube' => $hub->youtube,
  ])->map($safeUrl)->filter();

  $application = $application ?? $hub->applications()->latest()->first();
  $name = e(addslashes($hub->name));
@endphp

<div class="max-w-5xl mx-auto px-4 py-8 pb-12">

  <a href="{{ route('admin.hubs.index') }}" class="text-sm {{ $link }}">Back to hubs</a>

  {{-- Header --}}
  <div class="mt-3 mb-6 flex flex-col md:flex-row md:items-start justify-between gap-4">
    <div class="flex items-start gap-4 min-w-0">
      @if($hub->logo)
        <img src="{{ $hub->logo }}" alt="" class="w-16 h-16 shrink-0 rounded-xl object-cover border border-slate-100">
      @else
        <div class="w-16 h-16 shrink-0 rounded-xl bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center text-2xl font-bold" aria-hidden="true">{{ strtoupper(substr($hub->name,0,1)) }}</div>
      @endif
      <div class="min-w-0">
        <h1 class="text-2xl font-bold text-[#101E33]">{{ $hub->name }}</h1>
        <div class="mt-2 flex flex-wrap items-center gap-2">
          <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badge[$hub->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($hub->status) }}</span>
          @if($hub->is_verified)<span class="px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">Verified</span>@endif
          @if($place)<span class="text-sm text-slate-600">{{ $place }}</span>@endif
        </div>
      </div>
    </div>

    {{-- Actions: only the moves that make sense for the current status --}}
    <div class="flex flex-wrap gap-2 shrink-0">
      @if($hub->status === 'pending')
        <form method="POST" action="{{ route('admin.hubs.approve', $hub) }}" onsubmit="return confirm('Approve {{ $name }}?')">@csrf
          <button type="submit" class="{{ $btnApprove }}">Approve</button></form>
        <form method="POST" action="{{ route('admin.hubs.reject', $hub) }}" onsubmit="return confirm('Reject {{ $name }}?')">@csrf
          <button type="submit" class="{{ $btnReject }}">Reject</button></form>
      @elseif($hub->status === 'approved')
        <form method="POST" action="{{ route('admin.hubs.suspend', $hub) }}" onsubmit="return confirm('Suspend {{ $name }}?')">@csrf
          <button type="submit" class="{{ $btnSuspend }}">Suspend</button></form>
      @elseif($hub->status === 'suspended')
        <form method="POST" action="{{ route('admin.hubs.approve', $hub) }}" onsubmit="return confirm('Reinstate {{ $name }}?')">@csrf
          <button type="submit" class="{{ $btnRestore }}">Reinstate</button></form>
      @elseif($hub->status === 'rejected')
        <form method="POST" action="{{ route('admin.hubs.approve', $hub) }}" onsubmit="return confirm('Approve {{ $name }} after all?')">@csrf
          <button type="submit" class="{{ $btnRestore }}">Approve anyway</button></form>
      @endif

      @if($hub->is_public && $hub->status === 'approved')
        <a href="{{ route('hubs.show', $hub->slug) }}" class="{{ $btn }} border-slate-200 text-slate-700 bg-white hover:bg-slate-50">View public page</a>
      @endif
    </div>
  </div>

  <div class="grid md:grid-cols-3 gap-6 items-start">

    {{-- Main --}}
    <div class="md:col-span-2 space-y-6">
      <section class="{{ $card }}" aria-labelledby="desc-h">
        <h2 id="desc-h" class="{{ $h2 }}">Description</h2>
        @if($hub->description)
          <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $hub->description }}</p>
        @else
          <p class="text-slate-500">No description provided.</p>
        @endif
      </section>

      @if($hub->programs_offered)
        <section class="{{ $card }}" aria-labelledby="prog-h">
          <h2 id="prog-h" class="{{ $h2 }}">Programs offered</h2>
          <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $hub->programs_offered }}</p>
        </section>
      @endif

      <section class="{{ $card }}" aria-labelledby="app-h">
        <h2 id="app-h" class="{{ $h2 }}">Application</h2>
        @if($application)
          <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4">
              <dt class="text-slate-600">Status</dt>
              <dd><span class="px-2 py-1 rounded-full text-xs font-medium {{ $badge[$application->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($application->status) }}</span></dd>
            </div>
            @if($application->created_at)
              <div class="flex justify-between gap-4">
                <dt class="text-slate-600">Submitted</dt>
                <dd class="font-medium text-[#101E33]">{{ $application->created_at->format('M j, Y') }}
                  <span class="font-normal text-slate-500">({{ $application->created_at->diffForHumans() }})</span></dd>
              </div>
            @endif
            @if($application->review_notes)
              <div class="pt-3 border-t border-slate-100">
                <dt class="text-slate-600">Review notes</dt>
                <dd class="mt-1 text-slate-700 whitespace-pre-line">{{ $application->review_notes }}</dd>
              </div>
            @endif
          </dl>
        @else
          <p class="text-sm text-slate-500">No application on record for this hub.</p>
        @endif
      </section>

      @if($hub->teamMembers->isNotEmpty())
        <section class="{{ $card }}" aria-labelledby="team-h">
          <h2 id="team-h" class="{{ $h2 }}">Team members</h2>
          <ul class="divide-y divide-slate-100">
            @foreach($hub->teamMembers as $member)
              <li class="py-3 flex items-center justify-between gap-4">
                <div class="min-w-0">
                  <div class="font-medium text-[#101E33] truncate">{{ $member->user?->name ?? 'Removed user' }}</div>
                  @if($member->user?->email)
                    <div class="text-xs text-slate-500 break-all">{{ $member->user->email }}</div>
                  @endif
                </div>
                <span class="shrink-0 px-2 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">{{ ucfirst($member->role) }}</span>
              </li>
            @endforeach
          </ul>
        </section>
      @endif
    </div>

    {{-- Sidebar --}}
    <aside class="space-y-6" aria-label="Hub details">
      <section class="{{ $card }}" aria-labelledby="det-h">
        <h2 id="det-h" class="{{ $h2 }}">Details</h2>
        <dl class="space-y-3 text-sm">
          <div>
            <dt class="text-slate-600">Owner</dt>
            <dd class="font-medium text-[#101E33]">
              @if($hub->owner)
                {{ $hub->owner->name }}
                @if($hub->owner->email)<span class="block text-xs font-normal text-slate-500 break-all">{{ $hub->owner->email }}</span>@endif
              @else
                No owner
              @endif
            </dd>
          </div>
          <div><dt class="text-slate-600">Verified</dt><dd class="font-medium text-[#101E33]">{{ $hub->is_verified ? 'Yes' : 'No' }}</dd></div>
          <div><dt class="text-slate-600">Visibility</dt><dd class="font-medium text-[#101E33]">{{ $hub->is_public ? 'Public' : 'Not public' }}</dd></div>
          <div><dt class="text-slate-600">Location</dt><dd class="font-medium text-[#101E33]">{{ $place ?: 'Not set' }}</dd></div>
          @foreach(['Organization type' => $hub->organization_type, 'Community size' => $hub->community_size, 'Year established' => $hub->year_established] as $label => $value)
            @if($value)<div><dt class="text-slate-600">{{ $label }}</dt><dd class="font-medium text-[#101E33]">{{ $value }}</dd></div>@endif
          @endforeach
        </dl>
      </section>

      <section class="{{ $card }}" aria-labelledby="contact-h">
        <h2 id="contact-h" class="{{ $h2 }}">Contact</h2>
        <ul class="space-y-2 text-sm break-words">
          @if($hub->email)<li><a href="mailto:{{ $hub->email }}" class="{{ $link }}">{{ $hub->email }}</a></li>@endif
          @if($hub->phone)<li><a href="tel:{{ preg_replace('/[^\d+]/', '', $hub->phone) }}" class="{{ $link }}">{{ $hub->phone }}</a></li>@endif
          @if($website)<li><a href="{{ $website }}" target="_blank" rel="noopener noreferrer" class="{{ $link }}">{{ $hub->website }}<span class="sr-only"> (opens in a new tab)</span></a></li>@endif
          @foreach($socials as $label => $url)
            <li><a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="{{ $link }}">{{ $label }}<span class="sr-only"> (opens in a new tab)</span></a></li>
          @endforeach
          @unless($hub->email || $hub->phone || $website || $socials->isNotEmpty())
            <li class="text-slate-500">No contact details provided.</li>
          @endunless
        </ul>
      </section>
    </aside>
  </div>
</div>
@endsection