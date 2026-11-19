@extends('layouts.app')
@section('content')
@php
  $o = $opportunity;
  $card = 'bg-white rounded-2xl border border-slate-100 shadow-sm';
  $h2 = 'text-xl font-bold text-[#101E33] mb-3';
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
  $btn = 'block w-full text-center px-6 py-3 rounded-lg font-semibold transition '.$focus;

  // The application link is entered by whoever posted the opportunity: add https:// to bare domains
  // and refuse javascript:, data: and other schemes.
  $safeUrl = function ($u) {
    $u = trim((string) $u);
    if ($u === '') return null;
    if (preg_match('#^https?://#i', $u)) return $u;
    if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $u)) return null;
    return 'https://'.ltrim($u, '/');
  };
  $applyUrl = $safeUrl($o->application_link);

  $deadline = $o->deadline;
  $closed = $deadline && $deadline->copy()->endOfDay()->isPast();
  $soon = $deadline && ! $closed && now()->diffInDays($deadline) <= 7;
  $place = $o->location ?: ($o->is_remote ? 'Remote' : null);
  $sub = array_filter([$o->organization, $place]);
  $sent = session('status') ?? session('success');
@endphp

<section class="bg-[#101E33] text-white py-12 md:py-16 relative overflow-hidden">
  <div class="absolute inset-0 opacity-20" aria-hidden="true">
    <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-[#FF5C3D] blur-3xl"></div>
  </div>
  <div class="max-w-7xl mx-auto px-4 relative">
    <a href="/opportunities" class="text-sm text-slate-300 hover:text-white hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-white rounded">Back to opportunities</a>
    <div class="mt-4">
      <span class="inline-block text-xs px-3 py-1 rounded-full bg-white/10 border border-white/20">{{ $o->category?->name ?? 'Opportunity' }}</span>
    </div>
    <h1 class="mt-4 text-3xl md:text-4xl font-bold leading-tight">{{ $o->title }}</h1>
    @if($sub)<p class="mt-2 text-slate-300 text-lg">{{ implode(', ', $sub) }}</p>@endif
    <div class="mt-4 flex flex-wrap gap-2">
      @if($o->is_member_only)<span class="text-xs px-3 py-1 rounded-full bg-white text-[#101E33] font-medium">Members only</span>@endif
      @if($closed)
        <span class="text-xs px-3 py-1 rounded-full bg-white/10">Closed on {{ $deadline->format('M j, Y') }}</span>
      @elseif($deadline)
        <span class="text-xs px-3 py-1 rounded-full {{ $soon ? 'bg-[#FF5C3D] text-white font-medium' : 'bg-white/10' }}">{{ $soon ? 'Closing soon: ' : 'Deadline: ' }}{{ $deadline->format('M j, Y') }}</span>
      @endif
    </div>
  </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-10 grid md:grid-cols-3 gap-8 items-start">

  {{-- Main --}}
  <div class="md:col-span-2 space-y-6">
    <section class="{{ $card }} p-6 sm:p-8" aria-labelledby="about-h">
      <h2 id="about-h" class="{{ $h2 }}">About this opportunity</h2>
      @if($o->description)
        <p class="text-slate-700 leading-relaxed text-lg whitespace-pre-line">{{ $o->description }}</p>
      @else
        <p class="text-slate-500">No description provided.</p>
      @endif
    </section>

    @if($o->eligibility)
      <section class="{{ $card }} p-6 sm:p-8" aria-labelledby="elig-h">
        <h2 id="elig-h" class="{{ $h2 }}">Eligibility</h2>
        <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $o->eligibility }}</p>
      </section>
    @endif

    @if($o->hub)
      <section class="{{ $card }} p-6 sm:p-8" aria-labelledby="posted-h">
        <h2 id="posted-h" class="{{ $h2 }}">Posted by</h2>
        <a href="/hubs/{{ $o->hub->slug }}" class="flex items-center gap-4 rounded-xl hover:opacity-80 transition {{ $focus }}">
          @if($o->hub->logo)
            <img src="{{ $o->hub->logo }}" class="w-14 h-14 shrink-0 rounded-xl object-cover" alt="">
          @else
            <div class="w-14 h-14 shrink-0 rounded-xl bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center font-bold" aria-hidden="true">{{ strtoupper(substr($o->hub->name,0,1)) }}</div>
          @endif
          <div>
            <div class="font-semibold text-[#101E33]">{{ $o->hub->name }}</div>
            @if($o->hub->city || $o->hub->state)<div class="text-sm text-slate-600">{{ implode(', ', array_filter([$o->hub->city, $o->hub->state])) }}</div>@endif
          </div>
        </a>
      </section>
    @endif
  </div>

  {{-- Sidebar --}}
  <aside class="space-y-6 md:sticky md:top-24" aria-label="Apply and details">
    <section class="{{ $card }} p-6" aria-labelledby="apply-h">
      <h2 id="apply-h" class="text-lg font-bold text-[#101E33] mb-4">Apply</h2>

      @if($sent)
        <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-800 text-sm" role="status">{{ $sent }}</div>
      @endif

      <div class="space-y-3">
        @if($closed)
          <p class="text-sm text-slate-700 bg-slate-50 rounded-lg p-3">Applications for this opportunity closed on {{ $deadline->format('M j, Y') }}.</p>
        @else
          @auth
            @if($hasApplied)
              <div class="text-center px-6 py-3 bg-green-50 text-green-800 rounded-lg font-semibold" role="status">You have applied</div>
            @else
              <form method="POST" action="{{ route('opportunities.apply', $o) }}" onsubmit="this.querySelector('button[type=submit]').disabled = true">
                @csrf
                <label for="cover_letter" class="block text-sm font-medium text-slate-700 mb-1">Cover note <span class="font-normal text-slate-500">(optional)</span></label>
                <textarea id="cover_letter" name="cover_letter" rows="4" placeholder="Tell them why you are a good fit"
                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm mb-1 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">{{ old('cover_letter') }}</textarea>
                @error('cover_letter')<p class="mb-2 text-xs text-red-600">{{ $message }}</p>@enderror
                <button type="submit" class="{{ $btn }} mt-2 bg-[#FF5C3D] text-white hover:opacity-90 disabled:opacity-60 disabled:cursor-not-allowed">Apply on MWTT</button>
              </form>
            @endif
          @else
            <a href="{{ route('login') }}" class="{{ $btn }} bg-[#FF5C3D] text-white hover:opacity-90">Log in to apply</a>
            @if(\Illuminate\Support\Facades\Route::has('register'))
              <p class="text-xs text-slate-600 text-center">No account? <a href="{{ route('register') }}" class="text-[#FF5C3D] hover:underline">Join MWTT</a></p>
            @endif
          @endauth

          @if($applyUrl)
            <a href="{{ $applyUrl }}" target="_blank" rel="noopener noreferrer" class="{{ $btn }} border border-slate-200 text-slate-800 hover:bg-slate-50">External application link<span class="sr-only"> (opens in a new tab)</span></a>
          @endif
        @endif
      </div>
    </section>

    <section class="{{ $card }} p-6" aria-labelledby="details-h">
      <h2 id="details-h" class="text-lg font-bold text-[#101E33] mb-4">Quick details</h2>
      <dl class="space-y-4 text-sm">
        @foreach([
          'Category' => $o->category?->name,
          'Organization' => $o->organization,
          'Location' => $place,
          'Work mode' => $o->is_remote ? 'Remote' : 'Onsite',
          'Deadline' => $deadline ? $deadline->format('M j, Y') : 'Open',
        ] as $label => $value)
          @if($value)
            <div class="flex justify-between gap-3">
              <dt class="text-slate-600">{{ $label }}</dt>
              <dd class="font-medium text-[#101E33] text-right">{{ $value }}</dd>
            </div>
          @endif
        @endforeach
      </dl>
    </section>
  </aside>
</div>
@endsection