@extends('layouts.app')
@section('content')
@php
  $h = $hub;
  $card = 'bg-white rounded-2xl p-6 border border-slate-100 shadow-sm';
  $h2 = 'text-lg font-bold text-[#101E33] mb-3';
  $link = 'text-[#FF5C3D] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $row = 'hover:bg-slate-50 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#FF5C3D]';

  // Hub-supplied URLs: add https:// to bare domains, and refuse javascript:, data: and other schemes
  $safeUrl = function ($u) {
    $u = trim((string) $u);
    if ($u === '') return null;
    if (preg_match('#^https?://#i', $u)) return $u;
    if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $u)) return null;
    return 'https://'.ltrim($u, '/');
  };

  $location = implode(', ', array_filter([$h->city, $h->state, $h->country]));
  $website = $safeUrl($h->website);

  $socials = collect([
    'LinkedIn' => $h->linkedin, 'Facebook' => $h->facebook, 'Instagram' => $h->instagram,
    'X' => $h->x, 'YouTube' => $h->youtube,
  ])->map($safeUrl)->filter();

  // Details: rows with no value are left out. [label => [value, optional href]]
  $details = array_filter([
    'Email' => $h->email ? [$h->email, 'mailto:'.$h->email] : null,
    'Phone' => $h->phone ? [$h->phone, 'tel:'.preg_replace('/[^\d+]/', '', $h->phone)] : null,
    'Community size' => $h->community_size ? [$h->community_size, null] : null,
    'Year established' => $h->year_established ? [$h->year_established, null] : null,
    'Organization type' => $h->organization_type ? [$h->organization_type, null] : null,
  ]);
@endphp

{{-- Hero --}}
<section class="relative overflow-hidden aurora text-white py-12 md:py-16">
  <div class="absolute inset-0 tech-grid opacity-25" aria-hidden="true"></div>
  <div class="relative max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center md:items-start gap-6 text-center md:text-left">
    @if($h->logo)
      <img src="{{ $h->logo }}" class="w-24 h-24 shrink-0 rounded-2xl object-cover border-4 border-white/20" alt="">
    @else
      <div class="w-24 h-24 shrink-0 rounded-2xl bg-white/10 border-4 border-white/20 flex items-center justify-center text-3xl font-bold" aria-hidden="true">{{ strtoupper(substr($h->name,0,1)) }}</div>
    @endif
    <div class="min-w-0 flex-1">
      <h1 class="font-display text-3xl font-bold">{{ $h->name }}</h1>
      @if($location)<p class="text-slate-300 mt-1">{{ $location }}</p>@endif
      <div class="mt-3 flex flex-wrap items-center justify-center md:justify-start gap-3">
        @if($location)
          <a href="https://www.openstreetmap.org/search?query={{ urlencode($location) }}" target="_blank" rel="noopener noreferrer" class="text-sm px-3 py-1.5 rounded-lg border border-white/20 text-white font-medium hover:bg-white/10 transition">Find on map<span class="sr-only"> (opens in a new tab)</span></a>
        @endif
        @if($h->is_verified)<span class="text-xs px-2 py-1 rounded-full bg-green-500/20 text-green-200">Verified hub</span>@endif
        @if($website)
          <a href="{{ $website }}" target="_blank" rel="noopener noreferrer"
             class="text-sm px-3 py-1.5 rounded-lg bg-[#FF5C3D] text-white font-medium hover:opacity-90 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
            Visit website<span class="sr-only"> (opens in a new tab)</span>
          </a>
        @endif
      </div>
    </div>
  </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-10 grid md:grid-cols-3 gap-8 items-start">

  {{-- Main --}}
  <div class="md:col-span-2 space-y-6">
    <section class="{{ $card }}" aria-labelledby="about-h">
      <h2 id="about-h" class="{{ $h2 }}">About</h2>
      @if($h->description)
        <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $h->description }}</p>
      @else
        <p class="text-slate-500">No description provided.</p>
      @endif
    </section>

    @if($h->programs_offered)
      <section class="{{ $card }}" aria-labelledby="programs-h">
        <h2 id="programs-h" class="{{ $h2 }}">Programs offered</h2>
        <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $h->programs_offered }}</p>
      </section>
    @endif

    <section class="{{ $card }}" aria-labelledby="events-h">
      <h2 id="events-h" class="{{ $h2 }}">Upcoming events</h2>
      <ul class="space-y-1">
        @forelse($events as $event)
          <li>
            <a href="{{ route('events.show', $event->slug) }}" class="flex items-center gap-4 p-3 rounded-xl {{ $row }}">
              <div class="w-12 h-12 shrink-0 rounded-xl bg-[#FF5C3D]/10 text-[#FF5C3D] flex flex-col items-center justify-center leading-none" aria-hidden="true">
                <span class="text-[10px] font-semibold uppercase">{{ $event->start_date->format('M') }}</span>
                <span class="text-lg font-bold">{{ $event->start_date->format('j') }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="font-medium text-[#101E33] truncate">{{ $event->title }}</div>
                <div class="text-xs text-slate-500">{{ $event->start_date->format('M j, Y') }}{{ $event->is_online ? ', Online' : ($event->city ? ', '.$event->city : '') }}</div>
              </div>
            </a>
          </li>
        @empty
          <li class="text-slate-500 text-sm">No upcoming events listed for this hub yet.</li>
        @endforelse
      </ul>
    </section>
  </div>

  {{-- Sidebar --}}
  <aside class="space-y-6" aria-label="Hub details">
    @if($details)
      <section class="{{ $card }}" aria-labelledby="details-h">
        <h2 id="details-h" class="{{ $h2 }}">Details</h2>
        <dl class="space-y-3 text-sm">
          @foreach($details as $label => [$value, $href])
            <div>
              <dt class="text-slate-600">{{ $label }}</dt>
              <dd class="font-medium text-[#101E33] break-words">
                @if($href)<a href="{{ $href }}" class="{{ $link }}">{{ $value }}</a>@else{{ $value }}@endif
              </dd>
            </div>
          @endforeach
        </dl>
      </section>
    @endif

    @if($socials->isNotEmpty())
      <section class="{{ $card }}" aria-labelledby="social-h">
        <h2 id="social-h" class="{{ $h2 }}">Social</h2>
        <ul class="space-y-2 text-sm">
          @foreach($socials as $label => $url)
            <li><a href="{{ $url }}" class="{{ $link }}" target="_blank" rel="noopener noreferrer">{{ $label }}<span class="sr-only"> (opens in a new tab)</span></a></li>
          @endforeach
        </ul>
      </section>
    @endif
  </aside>
</div>
@endsection
