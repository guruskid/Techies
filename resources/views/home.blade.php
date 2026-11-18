@extends('layouts.app')
@section('content')
@php
  $card = 'bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition';
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
  $viewAll = 'text-[#FF5C3D] font-medium hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $primaryBtn = 'px-6 py-3 bg-[#FF5C3D] text-white font-semibold rounded-lg hover:opacity-90 transition text-center '.$focus;
  $input = 'w-full border border-slate-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  // Signed-in visitors get a way back into the app instead of "Join"
  $memberHome = \Illuminate\Support\Facades\Route::has('dashboard') ? route('dashboard') : route('profile.edit');
@endphp

{{-- Hero --}}
<section class="bg-[#101E33] text-white">
  <div class="max-w-7xl mx-auto px-4 py-14 md:py-20 pb-20 md:pb-24 grid md:grid-cols-2 gap-12 items-center">
    <div>
      <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-white/10 border border-white/20">Non-profit tech community, Nigeria</span>
      <h1 class="mt-5 text-4xl md:text-6xl font-bold leading-tight tracking-tight">Where Techies Connect, Collaborate and Grow.</h1>
      <p class="mt-5 text-lg text-slate-300 max-w-xl">Meet with the Techies is a growing technology community connecting professionals, developers, innovators, founders, students and technology hubs to build a stronger, more connected tech ecosystem.</p>
      <div class="mt-8 flex flex-wrap gap-3">
        @guest
          <a href="{{ route('register') }}" class="{{ $primaryBtn }} shadow-lg focus-visible:ring-offset-[#101E33]">Join the Community</a>
        @else
          <a href="{{ $memberHome }}" class="{{ $primaryBtn }} shadow-lg focus-visible:ring-offset-[#101E33]">Go to my dashboard</a>
        @endguest
        <a href="/members" class="px-6 py-3 border border-white/30 rounded-lg hover:bg-white/10 transition text-center focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Explore Our Network</a>
      </div>
    </div>
    <div class="hidden md:flex justify-center" aria-hidden="true">
      <svg viewBox="0 0 400 400" class="w-full max-w-md" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="200" cy="200" r="180" stroke="#FF5C3D" stroke-width="1" stroke-dasharray="4 6" opacity="0.4"/>
        <circle cx="200" cy="200" r="120" stroke="#06B6D4" stroke-width="1" stroke-dasharray="4 6" opacity="0.4"/>
        @foreach([[100,120],[280,100],[320,260],[120,300],[60,200],[260,320]] as $pt)
          <line x1="{{ $pt[0] }}" y1="{{ $pt[1] }}" x2="200" y2="200" stroke="#ffffff33" stroke-width="1"/>
          <circle cx="{{ $pt[0] }}" cy="{{ $pt[1] }}" r="14" fill="#FF5C3D"/>
        @endforeach
        <circle cx="200" cy="200" r="22" fill="#06B6D4"/>
      </svg>
    </div>
  </div>
</section>

{{-- Stats (shown once; the hero no longer repeats them) --}}
<section class="max-w-7xl mx-auto px-4 -mt-10 relative z-10" aria-label="Community at a glance">
  <dl class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach (['members'=>'Registered Techies','hubs'=>'Verified Hubs','chapters'=>'Chapters','events'=>'Events Organized'] as $k=>$label)
      <div class="bg-white rounded-2xl p-6 text-center shadow-xl border border-slate-100 flex flex-col-reverse">
        <dt class="text-sm text-slate-600 mt-1">{{ $label }}</dt>
        <dd class="text-3xl font-bold text-[#FF5C3D] tabular-nums">{{ number_format((int) $stats[$k]) }}</dd>
      </div>
    @endforeach
  </dl>
</section>

{{-- About --}}
<section class="max-w-7xl mx-auto px-4 py-14 md:py-20">
  <div class="max-w-3xl">
    <h2 class="text-3xl md:text-4xl font-bold text-[#101E33]">More Than a Meetup. A Connected Tech Community.</h2>
    <p class="mt-4 text-slate-600 text-lg">MWTT brings together developers, designers, engineers, founders, students and technology hubs to collaborate, learn and grow, connecting the entire technology ecosystem under one network.</p>
    <a href="/about" class="inline-block mt-6 font-semibold {{ $viewAll }}">Learn about us</a>
  </div>
</section>

{{-- Who we bring together --}}
<section class="bg-white py-14 md:py-20" aria-labelledby="who-h">
  <div class="max-w-7xl mx-auto px-4">
    <h2 id="who-h" class="text-3xl font-bold text-[#101E33] mb-8">Who We Bring Together</h2>
    <ul class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
      @foreach ([
        ['Developers','Building software and products that move the world.'],
        ['Designers','Crafting beautiful, usable digital experiences.'],
        ['Engineers','Solving hard technical problems at scale.'],
        ['Founders','Creating companies and communities.'],
        ['Product Managers','Turning ideas into shipped products.'],
        ['Tech Students','Learning and launching their careers.'],
        ['Tech Hubs','Running spaces where innovation happens.'],
        ['Innovation Communities','Growing ecosystems across regions.'],
      ] as [$title, $blurb])
        <li class="rounded-2xl border border-slate-100 p-5 hover:shadow-md transition">
          <div class="w-10 h-10 rounded-lg bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center font-bold" aria-hidden="true">{{ substr($title,0,1) }}</div>
          <h3 class="mt-3 font-semibold text-[#101E33]">{{ $title }}</h3>
          <p class="mt-1 text-sm text-slate-600">{{ $blurb }}</p>
        </li>
      @endforeach
    </ul>
  </div>
</section>

{{-- Members --}}
<section class="max-w-7xl mx-auto px-4 py-14 md:py-20" aria-labelledby="members-h">
  <div class="flex items-end justify-between gap-4 mb-8">
    <h2 id="members-h" class="text-3xl font-bold text-[#101E33]">Explore the Community</h2>
    <a href="/members" class="shrink-0 {{ $viewAll }}">View all<span class="sr-only"> members</span></a>
  </div>
  <ul class="grid md:grid-cols-3 gap-4">
    @forelse ($members as $m)
      @php $mp = $m->profile; $loc = implode(', ', array_filter([$mp?->city, $mp?->state])); @endphp
      <li>
        <a href="{{ $mp?->username ? $mp->publicUrl() : '/members' }}" class="{{ $card }} p-5 flex gap-4 items-center {{ $focus }}">
          @if($mp?->avatar)
            <img src="{{ $mp->avatar }}" alt="" loading="lazy" class="w-14 h-14 shrink-0 rounded-full object-cover">
          @else
            <div class="w-14 h-14 shrink-0 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold" aria-hidden="true">{{ strtoupper(substr($m->name,0,1)) }}</div>
          @endif
          <div class="min-w-0">
            <div class="font-semibold text-[#101E33] truncate">{{ $m->name }}</div>
            <div class="text-sm text-slate-600 truncate">{{ $mp?->professional_title ?? 'Member' }}</div>
            @if($loc)<div class="text-xs text-slate-500 truncate">{{ $loc }}</div>@endif
          </div>
        </a>
      </li>
    @empty
      <li class="text-slate-600 md:col-span-3">No public members yet. Be the first to join.</li>
    @endforelse
  </ul>
</section>

{{-- Hubs --}}
<section class="bg-[#F8FAFC] py-14 md:py-20" aria-labelledby="hubs-h">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex items-end justify-between gap-4 mb-8">
      <h2 id="hubs-h" class="text-3xl font-bold text-[#101E33]">Discover Technology Hubs</h2>
      <a href="/hubs" class="shrink-0 {{ $viewAll }}">View all<span class="sr-only"> hubs</span></a>
    </div>
    <ul class="grid md:grid-cols-3 gap-4">
      @forelse ($hubs as $h)
        @php $hloc = implode(', ', array_filter([$h->city, $h->state])); @endphp
        <li>
          <a href="/hubs/{{ $h->slug }}" class="{{ $card }} p-6 h-full flex flex-col {{ $focus }}">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 shrink-0 rounded-xl bg-slate-100 flex items-center justify-center font-bold text-slate-600" aria-hidden="true">{{ strtoupper(substr($h->name,0,1)) }}</div>
              <div class="min-w-0">
                <div class="font-semibold text-[#101E33]">{{ $h->name }}</div>
                @if($hloc)<div class="text-xs text-slate-500">{{ $hloc }}</div>@endif
              </div>
              @if($h->is_verified)<span class="ml-auto shrink-0 text-xs px-2 py-1 rounded-full bg-green-50 text-green-700 font-medium">Verified</span>@endif
            </div>
            <p class="mt-4 text-sm text-slate-600 flex-1">{{ \Illuminate\Support\Str::limit($h->description,110) }}</p>
            <span class="mt-4 text-sm font-medium text-[#FF5C3D]">View hub</span>
          </a>
        </li>
      @empty
        <li class="text-slate-600 md:col-span-3">No verified hubs yet.</li>
      @endforelse
    </ul>
  </div>
</section>

{{-- Events --}}
<section class="max-w-7xl mx-auto px-4 py-14 md:py-20" aria-labelledby="events-h">
  <div class="flex items-end justify-between gap-4 mb-8">
    <h2 id="events-h" class="text-3xl font-bold text-[#101E33]">Upcoming Events</h2>
    <a href="/events" class="shrink-0 {{ $viewAll }}">View all<span class="sr-only"> events</span></a>
  </div>
  <ul class="grid md:grid-cols-3 gap-4">
    @forelse ($events as $e)
      <li>
        <a href="/events/{{ $e->slug }}" class="{{ $card }} h-full flex gap-4 p-5 {{ $focus }}">
          <div class="w-14 h-14 shrink-0 rounded-xl bg-[#FF5C3D]/10 text-[#FF5C3D] flex flex-col items-center justify-center leading-none" aria-hidden="true">
            <span class="text-[11px] font-semibold uppercase">{{ $e->start_date->format('M') }}</span>
            <span class="text-xl font-bold">{{ $e->start_date->format('j') }}</span>
          </div>
          <div class="min-w-0">
            <div class="text-xs font-medium text-[#C23A1F]">{{ $e->start_date->format('M j, Y') }}{{ $e->is_online ? ', Online' : ($e->city ? ', '.$e->city : '') }}</div>
            <h3 class="mt-1 font-semibold text-[#101E33]">{{ $e->title }}</h3>
            <span class="inline-block mt-2 text-sm font-medium text-[#FF5C3D]">View event</span>
          </div>
        </a>
      </li>
    @empty
      <li class="text-slate-600 md:col-span-3">No upcoming events. Check back soon.</li>
    @endforelse
  </ul>
</section>

{{-- Opportunities --}}
<section class="bg-[#F8FAFC] py-14 md:py-20" aria-labelledby="opps-h">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex items-end justify-between gap-4 mb-8">
      <h2 id="opps-h" class="text-3xl font-bold text-[#101E33]">Community Opportunities</h2>
      <a href="/opportunities" class="shrink-0 {{ $viewAll }}">View all<span class="sr-only"> opportunities</span></a>
    </div>
    <ul class="grid md:grid-cols-3 gap-4">
      @forelse ($opportunities as $o)
        @php $meta = array_filter([$o->organization, $o->location ?: ($o->is_remote ? 'Remote' : null)]); @endphp
        <li>
          <a href="/opportunities/{{ $o->slug }}" class="{{ $card }} p-6 h-full flex flex-col {{ $focus }}">
            <span class="self-start text-xs px-2 py-1 rounded-full bg-[#FF5C3D]/10 text-[#C23A1F] font-medium">{{ $o->category?->name ?? 'Opportunity' }}</span>
            <h3 class="mt-3 font-semibold text-[#101E33]">{{ $o->title }}</h3>
            @if($meta)<p class="mt-1 text-sm text-slate-600 flex-1">{{ implode(', ', $meta) }}</p>@endif
            <span class="mt-4 text-sm font-medium text-[#FF5C3D]">View opportunity</span>
          </a>
        </li>
      @empty
        <li class="text-slate-600 md:col-span-3">No opportunities yet.</li>
      @endforelse
    </ul>
  </div>
</section>

{{-- Hub partner CTA --}}
<section class="max-w-7xl mx-auto px-4 py-14 md:py-20">
  <div class="bg-[#101E33] text-white rounded-3xl p-8 md:p-16 grid md:grid-cols-2 gap-8 items-center">
    <div>
      <h2 class="text-3xl md:text-4xl font-bold">Bring Your Tech Community Into the Network.</h2>
      <p class="mt-4 text-slate-300">Are you running a technology hub, innovation centre, developer community or university tech community? Join MWTT and connect with a wider technology ecosystem.</p>
      <a href="/hubs/register" class="inline-block mt-6 px-6 py-3 bg-[#FF5C3D] rounded-lg font-semibold hover:opacity-90 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Register Your Hub</a>
    </div>
    <ul class="space-y-3 text-slate-300 text-sm">
      @foreach(['Dedicated hub profile and public page','Publish events to the MWTT directory','Manage your team and community members','Showcase programs and opportunities'] as $perk)
        <li class="flex items-start gap-3">
          <svg class="w-5 h-5 shrink-0 text-[#FF5C3D]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
          <span>{{ $perk }}</span>
        </li>
      @endforeach
    </ul>
  </div>
</section>

{{-- Chapters --}}
<section class="max-w-7xl mx-auto px-4 pb-14 md:pb-20" aria-labelledby="chapters-h">
  <h2 id="chapters-h" class="text-3xl font-bold text-[#101E33] mb-6">Community Chapters</h2>
  <ul class="flex flex-wrap gap-3">
    @forelse ($chapters as $c)
      <li>
        <a href="/chapters/{{ $c->slug }}" class="inline-block px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:border-[#FF5C3D] hover:text-[#FF5C3D] transition {{ $focus }}">{{ $c->name }}</a>
      </li>
    @empty
      <li class="text-slate-600">No chapters yet.</li>
    @endforelse
  </ul>
</section>

{{-- Newsletter --}}
<section id="newsletter" class="bg-[#F8FAFC] py-14 md:py-20">
  <div class="max-w-2xl mx-auto px-4 text-center">
    <h2 class="text-3xl font-bold text-[#101E33]">Stay in the Loop</h2>
    <p class="mt-3 text-slate-600">Subscribe to community updates, events and opportunities.</p>
    <form method="POST" action="/newsletter" class="mt-6 flex flex-col sm:flex-row gap-2 text-left">
      @csrf
      <div class="flex-1">
        <label for="nl-name" class="sr-only">Your name</label>
        <input id="nl-name" name="name" value="{{ old('name') }}" placeholder="Your name" autocomplete="name" class="{{ $input }}" required>
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
      </div>
      <div class="flex-1">
        <label for="nl-email" class="sr-only">Your email</label>
        <input id="nl-email" name="email" type="email" value="{{ old('email') }}" placeholder="Your email" autocomplete="email" class="{{ $input }}" required>
        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
      </div>
      <button type="submit" class="self-start sm:self-auto px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition {{ $focus }}">Subscribe</button>
    </form>
  </div>
</section>

{{-- Final CTA --}}
<section class="max-w-7xl mx-auto px-4 py-14 md:py-20 text-center">
  <h2 class="text-3xl md:text-4xl font-bold text-[#101E33]">The Future of Tech Is Better Together.</h2>
  <div class="mt-8 flex flex-wrap justify-center gap-3">
    @guest
      <a href="{{ route('register') }}" class="{{ $primaryBtn }}">Join the Community</a>
    @else
      <a href="{{ $memberHome }}" class="{{ $primaryBtn }}">Go to my dashboard</a>
    @endguest
    <a href="/hubs/register" class="px-6 py-3 border border-slate-300 rounded-lg hover:bg-slate-50 transition text-center {{ $focus }}">Register Your Hub</a>
  </div>
</section>
@endsection