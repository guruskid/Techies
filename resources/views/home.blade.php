@extends('layouts.app')
@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
  $memberHome = \Illuminate\Support\Facades\Route::has('dashboard') ? route('dashboard') : route('profile.edit');
  $membershipFee = (int) \App\Models\Setting::value('membership_fee', '15000');
  $membershipCurrency = (string) \App\Models\Setting::value('membership_currency', 'NGN');
  $membershipPrice = $membershipCurrency === 'NGN' ? '₦'.number_format($membershipFee) : $membershipCurrency.' '.number_format($membershipFee);
@endphp

{{-- Hero --}}
<section class="relative overflow-hidden border-b border-slate-200 bg-white">
  <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(60rem_32rem_at_88%_-10%,rgba(255,92,61,.09),transparent_62%)]" aria-hidden="true"></div>
  <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(40rem_26rem_at_8%_110%,rgba(15,23,42,.05),transparent_60%)]" aria-hidden="true"></div>

  <div class="relative max-w-7xl mx-auto px-4 py-14 md:py-24 grid lg:grid-cols-[1.05fr_0.95fr] gap-12 items-center">
    <div class="min-w-0">
      <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-1.5 text-xs font-medium text-slate-600">
        <span class="h-2 w-2 rounded-full bg-[#FF5C3D]"></span>
        Nigeria's community for people in tech
      </div>
      <h1 class="mt-6 font-display text-4xl sm:text-5xl md:text-6xl font-extrabold leading-[1.04] text-[#101E33]">
        Build your career with people who get it.
      </h1>
      <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-600">
        Meet with the Techies connects developers, designers, founders, students and hubs across Nigeria for events, mentorship, opportunities and honest career support.
      </p>
      <div class="mt-8 flex flex-wrap items-center gap-3">
        @guest
          <a href="{{ route('membership.show') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#FF5C3D] px-6 py-3.5 font-semibold text-white transition hover:bg-[#e94f32] {{ $focus }}">
            Become a member
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
          </a>
        @else
          <a href="{{ $memberHome }}" class="inline-flex items-center gap-2 rounded-lg bg-[#FF5C3D] px-6 py-3.5 font-semibold text-white transition hover:bg-[#e94f32] {{ $focus }}">
            Open my dashboard
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
          </a>
        @endguest
        <a href="{{ route('members') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-6 py-3.5 font-semibold text-slate-700 transition hover:bg-slate-50 {{ $focus }}">
          Explore the network
        </a>
      </div>
      <p class="mt-5 text-sm text-slate-500">One-time membership · No monthly renewal · Built for the Nigerian tech ecosystem</p>
    </div>

    <div class="min-w-0">
      <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_30px_80px_-45px_rgba(15,23,42,.45)]">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-4">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#C23A1F]">What's happening</p>
            <p class="mt-1 text-sm text-slate-500">A snapshot of the community right now</p>
          </div>
          <a href="{{ route('events') }}" class="text-xs font-semibold text-[#C23A1F] hover:underline">View all</a>
        </div>

        <div class="divide-y divide-slate-100">
          @if($events->isNotEmpty())
            @php $nextEvent = $events->first(); @endphp
            <a href="{{ route('events.show', $nextEvent->slug) }}" class="flex items-center gap-4 py-4 transition hover:bg-slate-50/80 {{ $focus }} rounded-lg">
              <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 leading-none text-[#101E33]">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#C23A1F]">{{ $nextEvent->start_date->format('M') }}</span>
                <span class="font-display text-lg font-extrabold">{{ $nextEvent->start_date->format('j') }}</span>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Next event</p>
                <p class="line-clamp-2 font-semibold text-[#101E33]">{{ $nextEvent->title }}</p>
                <p class="text-xs text-slate-500">{{ $nextEvent->is_online ? 'Online' : ($nextEvent->city ?: 'Venue to be announced') }}</p>
              </div>
              <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
          @endif

          @if($opportunities->isNotEmpty())
            @php $nextOpportunity = $opportunities->first(); @endphp
            <a href="{{ route('opportunities.show', $nextOpportunity->slug) }}" class="flex items-center gap-4 py-4 transition hover:bg-slate-50/80 {{ $focus }} rounded-lg">
              <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#101E33] text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Opportunity</p>
                <p class="line-clamp-2 font-semibold text-[#101E33]">{{ $nextOpportunity->title }}</p>
                <p class="truncate text-xs text-slate-500">{{ $nextOpportunity->organization ?: 'Community opportunity' }}{{ $nextOpportunity->is_remote ? ' · Remote' : '' }}</p>
              </div>
              <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
          @endif

          @if($speakerCalls->isNotEmpty())
            @php $openCall = $speakerCalls->first(); @endphp
            <a href="{{ route('speaker-calls.show', $openCall->slug) }}" class="flex items-center gap-4 py-4 transition hover:bg-slate-50/80 {{ $focus }} rounded-lg">
              <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#FF5C3D]/10 text-[#C23A1F]">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18v-5m0 0a3 3 0 100-6 3 3 0 000 6zm-7 5a7 7 0 0114 0"/></svg>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Open call</p>
                <p class="line-clamp-2 font-semibold text-[#101E33]">{{ $openCall->title }}</p>
                <p class="text-xs text-slate-500">{{ $openCall->closes_at ? 'Closes '.$openCall->closes_at->format('M j, Y') : 'Open for submissions' }}</p>
              </div>
              <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
          @endif
        </div>

        <div class="flex items-center gap-3 border-t border-slate-100 pt-4">
          <div class="flex shrink-0 -space-x-2">
            @foreach($members->take(4) as $member)
              @if($member->profile?->avatar)
                <img src="{{ $member->profile->avatar }}" alt="" class="h-8 w-8 rounded-full border-2 border-white object-cover">
              @else
                <span class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-[#101E33] text-[10px] font-bold text-white">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
              @endif
            @endforeach
          </div>
          <p class="text-xs text-slate-500">{{ number_format((int) $stats['members']) }} members building across Nigeria.</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Stats --}}
<section class="border-b border-slate-200 bg-slate-50" aria-label="Community at a glance">
  <dl class="max-w-7xl mx-auto px-4 py-8 grid grid-cols-2 md:grid-cols-4 gap-6">
    @foreach (['members' => 'Members', 'hubs' => 'Verified hubs', 'chapters' => 'Chapters', 'events' => 'Events hosted'] as $key => $label)
      <div>
        <dd class="font-display text-3xl font-extrabold text-[#101E33] tabular-nums">{{ number_format((int) $stats[$key]) }}</dd>
        <dt class="mt-1 text-sm text-slate-500">{{ $label }}</dt>
      </div>
    @endforeach
  </dl>
</section>

{{-- Value pillars --}}
<section class="max-w-7xl mx-auto px-4 py-14 md:py-16">
  <div class="grid sm:grid-cols-3 gap-4">
    @foreach ([
      ['Meet', 'Find the people building the same future you are.', 'Connect with developers, designers, founders and students across the network.', route('members')],
      ['Learn', 'Events, workshops and honest conversations with practitioners.', 'Show up, ask questions and leave with something you can apply.', route('events')],
      ['Grow', 'Opportunities, mentorship and a stage when you are ready.', 'Move your career forward with support from people who have done it.', route('opportunities')],
    ] as [$title, $copy, $detail, $url])
      <a href="{{ $url }}" class="group rounded-2xl border border-slate-200 bg-white p-6 transition hover:border-[#FF5C3D]/40 hover:shadow-sm {{ $focus }}">
        <div class="flex items-center justify-between">
          <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-[#101E33]">
            <span class="h-2 w-2 rounded-full bg-[#FF5C3D]"></span>
          </span>
          <svg class="h-4 w-4 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-[#C23A1F]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </div>
        <h2 class="mt-4 font-display text-xl font-bold text-[#101E33]">{{ $title }}</h2>
        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $copy }}</p>
        <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $detail }}</p>
      </a>
    @endforeach
  </div>
</section>

{{-- Events --}}
<section class="max-w-7xl mx-auto px-4 py-16 md:py-20" aria-labelledby="events-h">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
    <div>
      <p class="section-label">What's next</p>
      <h2 id="events-h" class="mt-2 font-display text-3xl md:text-4xl font-bold text-[#101E33]">Upcoming events</h2>
    </div>
    <a href="{{ route('events') }}" class="text-sm font-semibold text-[#C23A1F] hover:underline {{ $focus }}">View all events</a>
  </div>

  <div class="grid md:grid-cols-3 gap-4">
    @forelse($events->take(3) as $event)
      <a href="{{ route('events.show', $event->slug) }}" class="card-lift group flex h-full flex-col rounded-2xl border border-slate-200/80 bg-white p-6 shadow-[0_22px_70px_-55px_rgba(10,18,32,.7)] {{ $focus }}">
        <div class="flex items-start justify-between gap-3">
          <div class="flex h-14 w-14 flex-col items-center justify-center rounded-xl bg-[#101E33] text-white leading-none">
            <span class="text-[10px] font-semibold uppercase tracking-wider text-[#FF8A73]">{{ $event->start_date->format('M') }}</span>
            <span class="font-display text-xl font-bold">{{ $event->start_date->format('j') }}</span>
          </div>
          <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-600">{{ $event->category?->name ?? 'Event' }}</span>
        </div>
        <h3 class="mt-4 font-display text-lg font-bold leading-snug text-[#101E33] group-hover:text-[#C23A1F]">{{ $event->title }}</h3>
        <p class="mt-2 text-sm text-slate-600">{{ $event->is_online ? 'Online' : ($event->city ?: 'Location to be announced') }}{{ $event->start_time ? ' · '.$event->start_time : '' }}</p>
        <span class="mt-auto pt-5 text-sm font-semibold text-[#C23A1F]">View event</span>
      </a>
    @empty
      <p class="text-slate-600 md:col-span-3">No upcoming events yet. Check back soon.</p>
    @endforelse
  </div>
</section>

{{-- Opportunities + speaker calls --}}
<section class="bg-white border-y border-slate-200/70 py-16 md:py-20">
  <div class="max-w-7xl mx-auto px-4 grid lg:grid-cols-[1.35fr_0.65fr] gap-8 items-start">
    <div>
      <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
        <div>
          <p class="section-label">Make your next move</p>
          <h2 class="mt-2 font-display text-3xl md:text-4xl font-bold text-[#101E33]">Opportunities</h2>
        </div>
        <a href="{{ route('opportunities') }}" class="text-sm font-semibold text-[#C23A1F] hover:underline {{ $focus }}">View all</a>
      </div>
      <div class="space-y-3">
        @forelse($opportunities->take(4) as $opportunity)
          @php $meta = array_filter([$opportunity->organization, $opportunity->location ?: ($opportunity->is_remote ? 'Remote' : null)]); @endphp
          <a href="{{ route('opportunities.show', $opportunity->slug) }}" class="card-lift group flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-2xl border border-slate-200/80 bg-[#F9FAFC] p-5 {{ $focus }}">
            <div class="min-w-0">
              <p class="text-[11px] font-semibold uppercase tracking-wider text-[#C23A1F]">{{ $opportunity->category?->name ?? 'Opportunity' }}</p>
              <h3 class="mt-1 font-display text-lg font-bold text-[#101E33] group-hover:text-[#C23A1F]">{{ $opportunity->title }}</h3>
              @if($meta)<p class="mt-1 text-sm text-slate-600">{{ implode(' · ', $meta) }}</p>@endif
            </div>
            <span class="shrink-0 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 group-hover:border-[#FF5C3D] group-hover:text-[#C23A1F]">View role</span>
          </a>
        @empty
          <p class="text-slate-600">No opportunities yet.</p>
        @endforelse
      </div>
    </div>

    <aside class="rounded-3xl bg-[#101E33] p-6 text-white">
      <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#FF8A73]">Speak</p>
      <h2 class="mt-2 font-display text-2xl font-bold">Have something worth sharing?</h2>
      <p class="mt-2 text-sm leading-relaxed text-slate-300">Open calls let you submit a talk, choose a topic and get a decision by email.</p>
      @if($speakerCalls->isNotEmpty())
        <ul class="mt-5 space-y-2">
          @foreach($speakerCalls as $call)
            <li>
              <a href="{{ route('speaker-calls.show', $call->slug) }}" class="block rounded-xl border border-white/10 bg-white/5 p-3 transition hover:bg-white/10 {{ $focus }} focus-visible:ring-offset-[#101E33]">
                <span class="block text-sm font-semibold">{{ $call->title }}</span>
                <span class="mt-0.5 block text-xs text-slate-400">{{ $call->closes_at ? 'Closes '.$call->closes_at->format('M j') : 'Open now' }}</span>
              </a>
            </li>
          @endforeach
        </ul>
      @else
        <p class="mt-5 text-sm text-slate-400">New calls are announced regularly.</p>
      @endif
      <a href="{{ route('speaker-calls.index') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-[#FF8A73] hover:text-white">Browse calls <span aria-hidden="true">&rarr;</span></a>
    </aside>
  </div>
</section>

{{-- Hubs and chapters --}}
<section class="max-w-7xl mx-auto px-4 py-16 md:py-20">
  <div class="grid lg:grid-cols-[1.4fr_0.6fr] gap-8 items-start">
    <div>
      <div class="flex items-end justify-between gap-4 mb-6">
        <div>
          <p class="section-label">Where it happens</p>
          <h2 class="mt-2 font-display text-3xl md:text-4xl font-bold text-[#101E33]">Technology hubs</h2>
        </div>
        <a href="{{ route('hubs') }}" class="text-sm font-semibold text-[#C23A1F] hover:underline {{ $focus }}">View all</a>
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        @forelse($hubs->take(4) as $hub)
          <a href="{{ route('hubs.show', $hub->slug) }}" class="card-lift group rounded-2xl border border-slate-200/80 bg-white p-5 {{ $focus }}">
            <div class="flex items-center gap-3">
              @if($hub->logo)
                <img src="{{ $hub->logo }}" alt="" class="h-11 w-11 rounded-xl object-cover border border-slate-100">
              @else
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#101E33] font-display text-lg font-bold text-white">{{ strtoupper(substr($hub->name, 0, 1)) }}</span>
              @endif
              <div class="min-w-0">
                <h3 class="truncate font-display font-bold text-[#101E33] group-hover:text-[#C23A1F]">{{ $hub->name }}</h3>
                <p class="text-xs text-slate-500">{{ implode(', ', array_filter([$hub->city, $hub->state])) ?: 'Nigeria' }}</p>
              </div>
            </div>
            <p class="mt-3 line-clamp-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags($hub->description), 100) }}</p>
          </a>
        @empty
          <p class="text-slate-600">No hubs yet.</p>
        @endforelse
      </div>
    </div>

    <aside class="rounded-3xl border border-slate-200/80 bg-white p-6">
      <p class="section-label">Local first</p>
      <h2 class="mt-2 font-display text-2xl font-bold text-[#101E33]">Chapters</h2>
      <p class="mt-2 text-sm text-slate-600">Find the community closest to you.</p>
      <ul class="mt-5 flex flex-wrap gap-2">
        @forelse($chapters as $chapter)
          <li>
            <a href="{{ route('chapters.show', $chapter->slug) }}" class="inline-flex rounded-full border border-slate-200 bg-[#F9FAFC] px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:border-[#FF5C3D] hover:text-[#C23A1F] {{ $focus }}">{{ $chapter->name }}</a>
          </li>
        @empty
          <li class="text-sm text-slate-600">No chapters yet.</li>
        @endforelse
      </ul>
      <a href="{{ route('chapters') }}" class="mt-5 inline-flex text-sm font-semibold text-[#C23A1F] hover:underline">See all chapters</a>
    </aside>
  </div>
</section>

{{-- Articles --}}
<section class="bg-white border-y border-slate-200/70 py-16 md:py-20">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
      <div>
        <p class="section-label">Ideas worth reading</p>
        <h2 class="mt-2 font-display text-3xl md:text-4xl font-bold text-[#101E33]">From the community</h2>
      </div>
      <a href="{{ route('articles') }}" class="text-sm font-semibold text-[#C23A1F] hover:underline {{ $focus }}">View all articles</a>
    </div>
    <div class="grid md:grid-cols-3 gap-4">
      @forelse($articles as $article)
        <a href="{{ route('articles.show', $article) }}" class="card-lift group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-[#F9FAFC] {{ $focus }}">
          <div class="aspect-[16/9] overflow-hidden bg-[#101E33]">
            @if($article->cover_image)
              <img src="{{ $article->cover_image }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
            @else
              <div class="flex h-full items-center justify-center bg-gradient-to-br from-[#101E33] to-[#1E3557] font-display text-2xl font-bold text-white/80">{{ $article->topicLabel() }}</div>
            @endif
          </div>
          <div class="flex flex-1 flex-col p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#C23A1F]">{{ $article->topicLabel() }}</p>
            <h3 class="mt-2 font-display text-lg font-bold leading-snug text-[#101E33] group-hover:text-[#C23A1F]">{{ $article->title }}</h3>
            <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $article->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($article->body), 120) }}</p>
            <p class="mt-auto pt-4 text-xs text-slate-500">{{ $article->readingTime() }} min read</p>
          </div>
        </a>
      @empty
        <p class="text-slate-600 md:col-span-3">No articles yet.</p>
      @endforelse
    </div>
  </div>
</section>

{{-- Membership CTA --}}
<section class="max-w-7xl mx-auto px-4 py-16 md:py-20">
  <div class="relative overflow-hidden rounded-3xl bg-[#101E33] p-8 md:p-12 text-white">
    <div class="absolute inset-0 tech-grid opacity-30" aria-hidden="true"></div>
    <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[#FF5C3D]/20 blur-3xl" aria-hidden="true"></div>
    <div class="relative grid lg:grid-cols-[1.2fr_0.8fr] gap-8 items-center">
      <div>
        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#FF8A73]">Membership</p>
        <h2 class="mt-2 font-display text-3xl md:text-4xl font-bold">Do the work. Find your people.</h2>
        <p class="mt-3 max-w-xl text-slate-300">One membership gives you access to the events, mentorship, opportunities, chapters and speaker stages that help technology careers compound.</p>
        <div class="mt-6 flex flex-wrap items-center gap-3">
          <span class="rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm font-semibold">{{ $membershipPrice }} once</span>
          <span class="text-sm text-slate-400">No monthly renewal</span>
        </div>
        <a href="{{ route('membership.show') }}" class="btn-glow mt-6 inline-flex items-center gap-2 rounded-xl bg-[#FF5C3D] px-6 py-3.5 font-semibold text-white transition hover:opacity-90 {{ $focus }} focus-visible:ring-offset-[#101E33]">
          See what is included
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
      </div>
      <ul class="space-y-3 text-sm text-slate-300">
        @foreach(['Member events, workshops and meetups', 'A public profile that showcases your work', 'Mentorship and peer learning circles', 'Opportunity applications and speaker calls'] as $benefit)
          <li class="flex items-start gap-3">
            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#FF5C3D]/15 text-[#FF8A73]">
              <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
            </span>
            {{ $benefit }}
          </li>
        @endforeach
      </ul>
    </div>
  </div>
</section>

{{-- Newsletter --}}
<section class="bg-white border-t border-slate-200/70 py-16">
  <div class="max-w-4xl mx-auto px-4 text-center">
    <p class="section-label">Stay close</p>
    <h2 class="mt-2 font-display text-3xl font-bold text-[#101E33]">The community, in your inbox.</h2>
    <p class="mt-2 text-slate-600">Events, opportunities and honest lessons from people building in tech.</p>
    <form method="POST" action="/newsletter" class="mx-auto mt-6 flex max-w-2xl flex-col sm:flex-row gap-2 text-left">
      @csrf
      <div class="flex-1">
        <label for="nl-name" class="sr-only">Your name</label>
        <input id="nl-name" name="name" value="{{ old('name') }}" placeholder="Your name" autocomplete="name" required class="w-full rounded-xl border border-slate-200 bg-[#F9FAFC] px-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
      </div>
      <div class="flex-1">
        <label for="nl-email" class="sr-only">Your email</label>
        <input id="nl-email" name="email" type="email" value="{{ old('email') }}" placeholder="Your email" autocomplete="email" required class="w-full rounded-xl border border-slate-200 bg-[#F9FAFC] px-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
      </div>
      <button type="submit" class="rounded-xl bg-[#101E33] px-6 py-3.5 font-semibold text-white transition hover:bg-[#1A2C48] {{ $focus }}">Subscribe</button>
    </form>
  </div>
</section>
@endsection