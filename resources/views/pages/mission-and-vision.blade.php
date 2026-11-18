@extends('layouts.app')
@section('content')
<section class="bg-[#101E33] text-white py-16">
  <div class="max-w-7xl mx-auto px-4">
    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-white/10 border border-white/20">Mission & Vision</span>
    <h1 class="mt-5 text-4xl font-bold">Why MWTT exists.</h1>
    <p class="mt-4 text-slate-300 max-w-2xl">Technology in Nigeria is full of talent, energy and ideas. MWTT exists to connect the people and communities behind that work so they can go further together.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-16 grid md:grid-cols-2 gap-6">
  <div class="rounded-2xl border border-slate-100 bg-white p-8 shadow-sm">
    <div class="w-12 h-12 rounded-xl bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center font-bold text-xl">M</div>
    <h2 class="mt-4 text-2xl font-bold text-[#101E33]">Our Mission</h2>
    <p class="mt-3 text-slate-600 leading-relaxed">To build a connected, inclusive and thriving technology community across Nigeria and Africa — empowering techies and tech communities to collaborate, learn and grow.</p>
  </div>
  <div class="rounded-2xl border border-slate-100 bg-white p-8 shadow-sm">
    <div class="w-12 h-12 rounded-xl bg-[#101E33]/10 text-[#101E33] flex items-center justify-center font-bold text-xl">V</div>
    <h2 class="mt-4 text-2xl font-bold text-[#101E33]">Our Vision</h2>
    <p class="mt-3 text-slate-600 leading-relaxed">A future where every techie and every tech community can connect, collaborate and grow together — with no one left behind in the digital economy.</p>
  </div>
</section>

<section class="bg-white py-16">
  <div class="max-w-7xl mx-auto px-4">
    <h2 class="text-2xl font-bold text-[#101E33]">What that looks like in practice</h2>
    <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
      @foreach ([
        ['Connect', 'Members, hubs and chapters can find each other and build real relationships.'],
        ['Collaborate', 'Shared events, programmes and partnerships across the network.'],
        ['Learn', 'Knowledge sharing, mentorship and practical learning opportunities.'],
        ['Grow', 'Opportunities, visibility and support for people and communities.'],
      ] as $pillar)
        <div class="rounded-2xl border border-slate-100 p-6">
          <h3 class="font-semibold text-[#101E33]">{{ $pillar[0] }}</h3>
          <p class="mt-2 text-sm text-slate-500">{{ $pillar[1] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-16">
  <div class="max-w-3xl">
    <h2 class="text-2xl font-bold text-[#101E33]">Our commitment</h2>
    <p class="mt-3 text-slate-600 leading-relaxed">MWTT is a non-profit community, not a marketplace. We do not sell access to the network or charge members simply to belong. Our work is guided by the needs of the community and the belief that technology ecosystems are stronger when they are open, connected and inclusive.</p>
  </div>
  <div class="mt-8 flex flex-wrap gap-3">
    <a href="{{ route('about') }}" class="px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">More about MWTT</a>
    <a href="{{ route('register') }}" class="px-5 py-2.5 border border-slate-200 rounded-lg font-semibold hover:bg-slate-50 transition">Join the community</a>
  </div>
</section>
@endsection
