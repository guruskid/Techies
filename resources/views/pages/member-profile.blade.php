@extends('layouts.app')
@section('title', $user->name.' — '.($profile->professional_title ?? 'Member'))
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($profile->headline ?: $profile->bio ?: 'View '.$user->name.'\'s member profile on Meet with the Techies.'), 155))
@section('canonical', $profile->is_public ? $profile->publicUrl() : url()->current())
@section('og_type', 'profile')
@section('content')
@php
  $p = $profile;
  $card = 'bg-white rounded-2xl p-6 border border-slate-100 shadow-sm';
  $h2 = 'text-lg font-bold text-[#101E33] mb-3';
  $link = 'text-[#FF5C3D] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';

  // Member-supplied URLs: only ever link http(s), never javascript: or data: URLs
  $safeUrl = fn ($u) => is_string($u) && preg_match('#^https?://#i', trim($u)) ? trim($u) : null;
  // Multi-line text fields (one entry per line)
  $lines = fn ($t) => array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $t))));
  $csv = fn ($t) => array_values(array_filter(array_map('trim', explode(',', (string) $t))));

  $location = implode(', ', array_filter([$p->city, $p->state, $p->country]));
  $isOwner = auth()->check() && auth()->id() === $user->id;

  $socials = collect([
    'LinkedIn' => $p->linkedin, 'GitHub' => $p->github, 'Facebook' => $p->facebook,
    'Twitter (X)' => $p->twitter, 'TikTok' => $p->tiktok,
  ])->map($safeUrl)->filter();

  // Compact facts for the sidebar; rows with no value are left out
  $facts = array_filter([
    'Experience level' => $p->experience_level,
    'Technology category' => $p->technology_category,
    'Current organization' => $p->current_organization,
    'Availability' => $p->availability,
  ]);
  $languages = $lines($p->languages);
  $education = $lines($p->education);
  $certifications = $lines($p->certifications);

  // One combined interests list (member-picked interests plus free-form areas)
  $interests = collect($user->interests->pluck('name'))
      ->merge((array) $p->areas_of_interest)->filter()->unique()->values();
@endphp

{{-- Hero --}}
<section class="relative overflow-hidden aurora text-white py-12 sm:py-16">
  <div class="absolute inset-0 tech-grid opacity-25" aria-hidden="true"></div>
  <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center md:items-start gap-6 text-center md:text-left">
    @if($p->avatar)
      <img src="{{ asset($p->avatar) }}" alt="" class="w-28 h-28 shrink-0 rounded-full object-cover border-4 border-white/20">
    @else
      <div class="w-28 h-28 shrink-0 rounded-full bg-white/10 border-4 border-white/20 flex items-center justify-center text-4xl font-bold" aria-hidden="true">
        {{ strtoupper(substr($user->name,0,1)) }}
      </div>
    @endif
    <div class="min-w-0 flex-1">
      <h1 class="font-display text-3xl font-bold">{{ $user->name }}</h1>
      <p class="text-slate-200 mt-1">{{ $p->professional_title ?? 'Member' }}@if($p->current_organization), {{ $p->current_organization }}@endif</p>
      @if($p->headline)<p class="text-slate-300 text-sm mt-2 max-w-2xl">{{ $p->headline }}</p>@endif
      @if($location)<p class="text-sm text-slate-300 mt-2">{{ $location }}</p>@endif
      @if(!$p->is_public)<span class="inline-block mt-3 text-xs px-2 py-1 rounded-full bg-yellow-500/20 text-yellow-200">Private profile</span>@endif
    </div>
    @if($isOwner)
      <a href="{{ route('profile.edit') }}" class="shrink-0 px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-sm hover:bg-white/15 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Edit my profile</a>
    @endif
  </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-10 grid md:grid-cols-3 gap-8 items-start">

  {{-- Main column: the story --}}
  <div class="md:col-span-2 space-y-6">

    @if($p->bio)
      <section class="{{ $card }}" aria-labelledby="about-h">
        <h2 id="about-h" class="{{ $h2 }}">About</h2>
        <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $p->bio }}</p>
      </section>
    @endif

    @if($user->workExperiences->count())
      <section class="{{ $card }}" aria-labelledby="work-h">
        <h2 id="work-h" class="{{ $h2 }}">Work experience</h2>
        <ol class="space-y-5">
          @foreach($user->workExperiences as $we)
            @php
              $meta = array_filter([$we->company, $we->location]);
              $end = $we->is_current ? 'Present' : $we->end_date?->format('M Y');
              $dates = collect([$we->start_date?->format('M Y'), $end])->filter()->implode(' to ');
            @endphp
            <li class="pl-4 border-l-2 border-[#FF5C3D]/30">
              <div class="font-semibold text-[#101E33]">{{ $we->title }}</div>
              @if($meta)<div class="text-sm text-slate-600">{{ implode(', ', $meta) }}</div>@endif
              @if($dates)<div class="text-xs text-slate-500">{{ $dates }}</div>@endif
              @if($we->description)<p class="text-sm text-slate-700 mt-2 whitespace-pre-line">{{ $we->description }}</p>@endif
            </li>
          @endforeach
        </ol>
      </section>
    @endif

    @if($user->projects->count())
      <section class="{{ $card }}" aria-labelledby="proj-h">
        <h2 id="proj-h" class="{{ $h2 }}">Projects</h2>
        <ul class="grid sm:grid-cols-2 gap-4">
          @foreach($user->projects as $pr)
            @php $url = $safeUrl($pr->url); $tech = $csv($pr->technologies); @endphp
            <li class="rounded-xl border border-slate-100 p-4 flex flex-col">
              <div class="font-semibold text-[#101E33]">{{ $pr->title }}</div>
              @if($pr->description)<p class="text-sm text-slate-700 mt-1">{{ $pr->description }}</p>@endif
              @if($tech)
                <ul class="mt-2 flex flex-wrap gap-1">
                  @foreach($tech as $t)<li class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-xs">{{ $t }}</li>@endforeach
                </ul>
              @endif
              @if($url)
                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-block text-sm {{ $link }}">View project<span class="sr-only"> (opens in a new tab)</span></a>
              @endif
            </li>
          @endforeach
        </ul>
      </section>
    @endif

    @if($education || $certifications)
      <section class="{{ $card }}" aria-labelledby="edu-h">
        <h2 id="edu-h" class="{{ $h2 }}">Education and certifications</h2>
        <div class="grid sm:grid-cols-2 gap-6 text-sm">
          @if($education)
            <div>
              <h3 class="text-slate-600 mb-1">Education</h3>
              <ul class="space-y-1 font-medium text-[#101E33]">@foreach($education as $line)<li>{{ $line }}</li>@endforeach</ul>
            </div>
          @endif
          @if($certifications)
            <div>
              <h3 class="text-slate-600 mb-1">Certifications</h3>
              <ul class="space-y-1 font-medium text-[#101E33]">@foreach($certifications as $line)<li>{{ $line }}</li>@endforeach</ul>
            </div>
          @endif
        </div>
      </section>
    @endif
  </div>

  {{-- Sidebar: quick facts --}}
  <aside class="space-y-6" aria-label="Profile details">

    @if(!$isOwner)
      <section class="{{ $card }}" aria-labelledby="contact-h">
        <h2 id="contact-h" class="{{ $h2 }}">Contact {{ \Illuminate\Support\Str::of($user->name)->before(' ') }}</h2>
        @if($errors->any())
          <div class="mb-3 rounded-lg bg-red-50 border border-red-100 p-3 text-sm text-red-700" role="alert">
            <ul class="list-disc list-inside">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
        <form method="POST" action="{{ route('members.contact', $profile->username) }}" class="space-y-3">
          @csrf
          <div class="hidden" aria-hidden="true">
            <label for="contact-website">Website</label>
            <input id="contact-website" type="text" name="website" tabindex="-1" autocomplete="off">
          </div>
          @guest
            <div>
              <label for="contact-name" class="block text-sm font-medium text-slate-700 mb-1">Your name</label>
              <input id="contact-name" name="name" value="{{ old('name') }}" required maxlength="120"
                     class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
            </div>
            <div>
              <label for="contact-email" class="block text-sm font-medium text-slate-700 mb-1">Your email</label>
              <input id="contact-email" type="email" name="email" value="{{ old('email') }}" required maxlength="255"
                     class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
            </div>
          @endguest
          <div>
            <label for="contact-subject" class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
            <input id="contact-subject" name="subject" value="{{ old('subject') }}" required maxlength="150"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          </div>
          <div>
            <label for="contact-message" class="block text-sm font-medium text-slate-700 mb-1">Message</label>
            <textarea id="contact-message" name="message" rows="4" required maxlength="2000"
                      class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">{{ old('message') }}</textarea>
          </div>
          <button class="w-full px-4 py-2.5 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition">Send message</button>
          <p class="text-xs text-slate-500">
            @auth
              This emails {{ \Illuminate\Support\Str::of($user->name)->before(' ') }} directly. Your email address is included so they can reply.
            @else
              This emails {{ \Illuminate\Support\Str::of($user->name)->before(' ') }} directly. Your email address is included so they can reply.
            @endauth
          </p>
        </form>
      </section>
    @endif

    @if($facts || $languages)
      <section class="{{ $card }}" aria-labelledby="facts-h">
        <h2 id="facts-h" class="{{ $h2 }}">Details</h2>
        <dl class="space-y-3 text-sm">
          @foreach($facts as $label => $value)
            <div><dt class="text-slate-600">{{ $label }}</dt><dd class="font-medium text-[#101E33]">{{ $value }}</dd></div>
          @endforeach
          @if($languages)
            <div><dt class="text-slate-600">Languages</dt><dd class="font-medium text-[#101E33]">{{ implode(', ', $languages) }}</dd></div>
          @endif
        </dl>
      </section>
    @endif

    @if($socials->isNotEmpty())
      <section class="{{ $card }}" aria-labelledby="links-h">
        <h2 id="links-h" class="{{ $h2 }}">Links</h2>
        <ul class="space-y-2 text-sm">
          @foreach($socials as $label => $url)
            <li><a href="{{ $url }}" class="{{ $link }}" target="_blank" rel="noopener noreferrer">{{ $label }}<span class="sr-only"> (opens in a new tab)</span></a></li>
          @endforeach
        </ul>
      </section>
    @endif

    @if($user->chapters->count())
      <section class="{{ $card }}" aria-labelledby="chap-h">
        <h2 id="chap-h" class="{{ $h2 }}">Chapters</h2>
        <ul class="space-y-1 text-sm">
          @foreach($user->chapters as $ch)
            <li><a href="/chapters/{{ $ch->slug }}" class="{{ $link }}">{{ $ch->name }}</a></li>
          @endforeach
        </ul>
      </section>
    @endif

    @if($user->skills->count())
      <section class="{{ $card }}" aria-labelledby="skills-h">
        <h2 id="skills-h" class="{{ $h2 }}">Skills</h2>
        <ul class="flex flex-wrap gap-2">
          @foreach($user->skills as $sk)
            <li class="px-3 py-1 rounded-full bg-[#FF5C3D]/10 text-[#C23A1F] text-xs font-medium">{{ $sk->name }}</li>
          @endforeach
        </ul>
      </section>
    @endif

    @if($interests->isNotEmpty())
      <section class="{{ $card }}" aria-labelledby="int-h">
        <h2 id="int-h" class="{{ $h2 }}">Interests</h2>
        <ul class="flex flex-wrap gap-2">
          @foreach($interests as $it)
            <li class="px-3 py-1 rounded-full bg-[#101E33]/10 text-[#101E33] text-xs font-medium">{{ $it }}</li>
          @endforeach
        </ul>
      </section>
    @endif

    @if($p->likes)
      <section class="{{ $card }}" aria-labelledby="likes-h">
        <h2 id="likes-h" class="{{ $h2 }}">Likes and hobbies</h2>
        <p class="text-slate-700 text-sm whitespace-pre-line">{{ $p->likes }}</p>
      </section>
    @endif
  </aside>
</div>
@endsection