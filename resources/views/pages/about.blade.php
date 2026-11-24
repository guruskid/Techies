@extends('layouts.app')
@section('content')
@php
  // Prefer $stats from the controller (same shape as the home page). Falls back to queries so this view still works on its own.
  $stats = $stats ?? [
    'members'  => \App\Models\User::where('status','active')->count(),
    'hubs'     => \App\Models\Hub::where('status','approved')->where('is_verified',true)->count(),
    'chapters' => \App\Models\Chapter::where('status','active')->count(),
    'events'   => \App\Models\Event::where('status','published')->count(),
  ];
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
  $primaryBtn = 'px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition text-center '.$focus;
  $memberHome = \Illuminate\Support\Facades\Route::has('dashboard') ? route('dashboard') : route('profile.edit');
@endphp

{{-- Hero --}}
<section class="relative overflow-hidden aurora text-white py-14 md:py-20">
  <div class="absolute inset-0 tech-grid opacity-25" aria-hidden="true"></div>
  <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-10 items-center">
    <div>
      <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-white/10 border border-white/20">About MWTT</span>
      <h1 class="mt-5 font-display text-4xl md:text-5xl font-bold leading-tight">More Than a Meetup. A Connected Tech Community.</h1>
      <p class="mt-5 text-slate-300 text-lg max-w-xl">Meet with the Techies is a non-profit technology community built to connect people, organizations, technology hubs, innovation centres, developers, designers, founders, students and technology enthusiasts.</p>
    </div>
    <dl class="grid grid-cols-2 gap-4">
      @foreach(['members' => 'Registered Techies', 'hubs' => 'Verified Hubs', 'chapters' => 'Chapters', 'events' => 'Events'] as $key => $label)
        <div class="bg-white/5 border border-white/10 rounded-2xl p-5 sm:p-6 flex flex-col-reverse">
          <dt class="text-sm text-slate-300 mt-1">{{ $label }}</dt>
          <dd class="text-3xl font-bold text-[#FF5C3D] tabular-nums">{{ number_format((int) $stats[$key]) }}</dd>
        </div>
      @endforeach
    </dl>
  </div>
</section>

{{-- Story --}}
<section class="max-w-7xl mx-auto px-4 py-14 md:py-20 grid md:grid-cols-3 gap-8 items-start" aria-labelledby="story-h">
  <div class="md:col-span-2 max-w-3xl">
    <h2 id="story-h" class="text-3xl font-bold text-[#101E33]">Our Story</h2>
    <p class="mt-4 text-slate-700 text-lg leading-relaxed">Meet with the Techies (MWTT) was established to bring together the many parts of the technology ecosystem under one connected network. From individual developers and designers to technology hubs, innovation centres, founders, students and tech communities, MWTT exists to connect, collaborate and grow together.</p>
    <p class="mt-4 text-slate-700 leading-relaxed">Our focus is on building relationships, sharing knowledge, creating opportunities and strengthening the tech ecosystem across Nigeria and, in time, across Africa.</p>
  </div>
  <aside class="rounded-2xl bg-[#FF5C3D]/5 border border-[#FF5C3D]/20 p-6">
    <h3 class="font-bold text-[#101E33]">Non-profit by design</h3>
    <p class="mt-2 text-sm text-slate-700 leading-relaxed">We are a non-profit community organization, not a commercial marketplace.</p>
  </aside>
</section>

{{-- Mission and vision --}}
<section class="bg-white py-14 md:py-20" aria-label="Mission and vision">
  <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-8">
    <div class="rounded-2xl border border-slate-100 p-8 shadow-sm">
      <div class="w-12 h-12 rounded-xl bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center font-bold text-xl" aria-hidden="true">M</div>
      <h2 class="mt-4 text-2xl font-bold text-[#101E33]">Our Mission</h2>
      <p class="mt-3 text-slate-700 leading-relaxed">To build a connected, inclusive and thriving technology community across Nigeria and Africa, empowering techies and tech communities to collaborate, learn and grow.</p>
    </div>
    <div class="rounded-2xl border border-slate-100 p-8 shadow-sm">
      <div class="w-12 h-12 rounded-xl bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center font-bold text-xl" aria-hidden="true">V</div>
      <h2 class="mt-4 text-2xl font-bold text-[#101E33]">Our Vision</h2>
      <p class="mt-3 text-slate-700 leading-relaxed">A future where every techie and every tech community can connect, collaborate and grow together, with no one left behind in the digital economy.</p>
    </div>
  </div>
</section>

{{-- What we do --}}
<section class="max-w-7xl mx-auto px-4 py-14 md:py-20" aria-labelledby="do-h">
  <h2 id="do-h" class="text-3xl font-bold text-[#101E33] mb-8">What We Do</h2>
  <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ([
      ['Connect Techies','Bring professionals, developers, designers, founders and students into one network.'],
      ['Partner with Hubs','Verify and showcase technology hubs and innovation centres.'],
      ['Organize Events','Host meetups, conferences, workshops, hackathons and webinars.'],
      ['Share Opportunities','Curate jobs, internships, scholarships, grants and fellowships.'],
      ['Run Chapters','Support regional and state chapters across Nigeria.'],
      ['Build Community','Foster collaboration, mentorship and professional growth.'],
    ] as [$title, $blurb])
      <li class="rounded-2xl border border-slate-100 p-6 hover:shadow-md transition">
        <div class="w-10 h-10 rounded-lg bg-[#101E33] text-white flex items-center justify-center font-bold" aria-hidden="true">{{ substr($title,0,1) }}</div>
        <h3 class="mt-3 font-semibold text-[#101E33]">{{ $title }}</h3>
        <p class="mt-1 text-sm text-slate-600">{{ $blurb }}</p>
      </li>
    @endforeach
  </ul>
</section>

{{-- Values --}}
<section class="bg-[#F8FAFC] py-14 md:py-20" aria-labelledby="values-h">
  <div class="relative max-w-7xl mx-auto px-4">
    <h2 id="values-h" class="text-3xl font-bold text-[#101E33] text-center">Our Core Values</h2>
    <ul class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach ([
        ['Inclusion','We welcome everyone, regardless of background, skill level or experience. Every techie has a place in MWTT.'],
        ['Collaboration','We believe the best outcomes come from working together, sharing knowledge and building in the open.'],
        ['Innovation','We encourage creative thinking, experimentation and the pursuit of new ideas that move the ecosystem forward.'],
        ['Integrity','We act with honesty, transparency and accountability in everything we do as a community.'],
        ['Growth','We are committed to the continuous learning and professional development of every member.'],
        ['Community','We put people first, building lasting relationships and a sense of belonging across the tech ecosystem.'],
      ] as [$title, $blurb])
        <li class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition">
          <div class="w-10 h-10 rounded-lg bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center font-bold" aria-hidden="true">{{ substr($title,0,1) }}</div>
          <h3 class="mt-3 text-[#C23A1F] font-bold text-lg">{{ $title }}</h3>
          <p class="mt-2 text-sm text-slate-700 leading-relaxed">{{ $blurb }}</p>
        </li>
      @endforeach
    </ul>
  </div>
</section>

{{-- Call to action --}}
<section class="max-w-7xl mx-auto px-4 py-14 md:py-20 text-center">
  <h2 class="text-3xl font-bold text-[#101E33]">Join the Movement</h2>
  <p class="mt-3 text-slate-600">Be part of a growing technology community.</p>
  <div class="mt-6 flex flex-wrap justify-center gap-3">
    @guest
      <a href="{{ route('register') }}" class="{{ $primaryBtn }}">Join the Community</a>
    @else
      <a href="{{ $memberHome }}" class="{{ $primaryBtn }}">Go to my dashboard</a>
    @endguest
    <a href="/hubs/register" class="px-6 py-3 border border-slate-300 rounded-lg hover:bg-slate-50 transition text-center {{ $focus }}">Register Your Hub</a>
  </div>
</section>
@endsection