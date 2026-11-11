@extends('layouts.app')
@section('content')
<section class="bg-[#101E33] text-white py-20">
  <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-10 items-center">
    <div>
      <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-white/10 border border-white/20">About MWTT</span>
      <h1 class="mt-5 text-4xl md:text-5xl font-bold leading-tight">More Than a Meetup. A Connected Tech Community.</h1>
      <p class="mt-5 text-slate-300 text-lg max-w-xl">Meet with the Techies is a non-profit technology community built to connect people, organizations, technology hubs, innovation centres, developers, designers, founders, students and technology enthusiasts.</p>
    </div>
    <div class="hidden md:grid grid-cols-2 gap-4">
      <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
        <div class="text-3xl font-bold text-[#FF5C3D]">{{ \App\Models\User::where('role','member')->count() }}+</div>
        <div class="text-sm text-slate-300 mt-1">Registered Techies</div>
      </div>
      <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
        <div class="text-3xl font-bold text-[#FF5C3D]">{{ \App\Models\Hub::where('is_verified',true)->count() }}+</div>
        <div class="text-sm text-slate-300 mt-1">Verified Hubs</div>
      </div>
      <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
        <div class="text-3xl font-bold text-[#FF5C3D]">{{ \App\Models\Chapter::count() }}+</div>
        <div class="text-sm text-slate-300 mt-1">Chapters</div>
      </div>
      <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
        <div class="text-3xl font-bold text-[#FF5C3D]">{{ \App\Models\Event::count() }}+</div>
        <div class="text-sm text-slate-300 mt-1">Events</div>
      </div>
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-20">
  <div class="max-w-3xl">
    <h2 class="text-3xl font-bold text-[#101E33]">Our Story</h2>
    <p class="mt-4 text-slate-600 text-lg leading-relaxed">Meet with the Techies (MWTT) was established to bring together the many parts of the technology ecosystem under one connected network. From individual developers and designers to technology hubs, innovation centres, founders, students and tech communities — MWTT exists to connect, collaborate and grow together.</p>
    <p class="mt-4 text-slate-600 leading-relaxed">We are a non-profit community organization, not a commercial marketplace. Our focus is on building relationships, sharing knowledge, creating opportunities and strengthening the tech ecosystem across Nigeria and, in time, across Africa.</p>
  </div>
</section>

<section class="bg-white py-20">
  <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-8">
    <div class="rounded-2xl border border-slate-100 p-8 shadow-sm">
      <div class="w-12 h-12 rounded-xl bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center font-bold text-xl">M</div>
      <h3 class="mt-4 text-2xl font-bold text-[#101E33]">Our Mission</h3>
      <p class="mt-3 text-slate-600 leading-relaxed">To build a connected, inclusive and thriving technology community across Nigeria and Africa — empowering techies and tech communities to collaborate, learn and grow.</p>
    </div>
    <div class="rounded-2xl border border-slate-100 p-8 shadow-sm">
      <div class="w-12 h-12 rounded-xl bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center font-bold text-xl">V</div>
      <h3 class="mt-4 text-2xl font-bold text-[#101E33]">Our Vision</h3>
      <p class="mt-3 text-slate-600 leading-relaxed">A future where every techie and every tech community can connect, collaborate and grow together — with no one left behind in the digital economy.</p>
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-20">
  <h2 class="text-3xl font-bold text-[#101E33] mb-8">What We Do</h2>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ([
      ['Connect Techies','Bring professionals, developers, designers, founders and students into one network.'],
      ['Partner with Hubs','Verify and showcase technology hubs and innovation centres.'],
      ['Organize Events','Host meetups, conferences, workshops, hackathons and webinars.'],
      ['Share Opportunities','Curate jobs, internships, scholarships, grants and fellowships.'],
      ['Run Chapters','Support regional and state chapters across Nigeria.'],
      ['Build Community','Foster collaboration, mentorship and professional growth.'],
    ] as $item)
    <div class="rounded-2xl border border-slate-100 p-6 hover:shadow-md transition">
      <div class="w-10 h-10 rounded-lg bg-[#101E33] text-white flex items-center justify-center font-bold">{{ substr($item[0],0,1) }}</div>
      <h3 class="mt-3 font-semibold text-[#101E33]">{{ $item[0] }}</h3>
      <p class="mt-1 text-sm text-slate-500">{{ $item[1] }}</p>
    </div>
    @endforeach
  </div>
</section>

<section class="bg-[#F8FAFC] py-20">
  <div class="max-w-7xl mx-auto px-4 text-center">
    <h2 class="text-3xl font-bold text-[#101E33]">Our Core Values</h2>
    <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach ([
        ['Inclusion','We welcome everyone — regardless of background, skill level, or experience. Every techie has a place in MWTT.'],
        ['Collaboration','We believe the best outcomes come from working together, sharing knowledge and building in the open.'],
        ['Innovation','We encourage creative thinking, experimentation and the pursuit of new ideas that move the ecosystem forward.'],
        ['Integrity','We act with honesty, transparency and accountability in everything we do as a community.'],
        ['Growth','We are committed to the continuous learning and professional development of every member.'],
        ['Community','We put people first — building lasting relationships and a sense of belonging across the tech ecosystem.'],
      ] as $v)
      <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition">
        <div class="w-10 h-10 rounded-lg bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center font-bold">{{ substr($v[0],0,1) }}</div>
        <div class="mt-3 text-[#FF5C3D] font-bold text-lg">{{ $v[0] }}</div>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $v[1] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-20 text-center">
  <h2 class="text-3xl font-bold text-[#101E33]">Join the Movement</h2>
  <p class="mt-3 text-slate-600">Be part of a growing technology community.</p>
  <div class="mt-6 flex justify-center gap-3">
    <a href="{{ route('register') }}" class="px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Join the Community</a>
    <a href="/hubs/register" class="px-6 py-3 border border-slate-300 rounded-lg hover:bg-slate-50 transition">Register Your Hub</a>
  </div>
</section>
@endsection
