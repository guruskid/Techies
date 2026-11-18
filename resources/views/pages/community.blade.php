@extends('layouts.app')
@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
  // Signed-in members should edit their profile rather than be sent to register
  $profileUrl = auth()->check() ? route('profile.edit') : '/register';
  $profileLabel = auth()->check() ? 'Edit your profile' : 'Create a profile';
@endphp

<section class="bg-[#101E33] text-white py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-4">
    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-white/10 border border-white/20">Community</span>
    <h1 class="mt-5 text-4xl font-bold">One community. Many ways to belong.</h1>
    <p class="mt-4 text-slate-300 max-w-2xl">MWTT brings together developers, designers, founders, students, technology hubs and community organisers. However you work in tech, there is a place for you here.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-12 md:py-16" aria-label="Ways to get involved">
  <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ([
      ['Find your people', 'Browse member profiles, discover shared interests and connect with techies across Nigeria.', '/members', 'Explore members'],
      ['Join a chapter', 'Meet locally, attend events and help grow the technology community in your state.', '/chapters', 'View chapters'],
      ['Discover hubs', 'Find technology hubs, innovation centres and communities doing great work near you.', '/hubs', 'Explore hubs'],
      ['Attend events', 'Meetups, workshops, webinars, hackathons and career events across the network.', '/events', 'See events'],
      ['Grow your career', 'Jobs, internships, scholarships, grants, fellowships and mentorship opportunities.', '/opportunities', 'Find opportunities'],
      ['Share your story', 'Your profile works like a CV and helps others understand what you do and how to collaborate.', $profileUrl, $profileLabel],
    ] as [$title, $blurb, $url, $cta])
      <li>
        <a href="{{ $url }}" class="h-full flex flex-col bg-white rounded-2xl border border-slate-100 p-6 shadow-sm hover:shadow-md transition {{ $focus }}">
          <h2 class="font-semibold text-[#101E33]">{{ $title }}</h2>
          <p class="mt-2 text-sm text-slate-600 flex-1">{{ $blurb }}</p>
          <span class="mt-4 text-sm font-medium text-[#FF5C3D]">{{ $cta }}</span>
        </a>
      </li>
    @endforeach
  </ul>
</section>

<section class="bg-white py-12 md:py-16" aria-labelledby="show-up-h">
  <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-10 items-start">
    <div>
      <h2 id="show-up-h" class="text-2xl font-bold text-[#101E33]">How we show up</h2>
      <p class="mt-3 text-slate-700 leading-relaxed">A community is only as strong as the way its members treat one another. We ask everyone to bring curiosity, respect and generosity.</p>
      <ul class="mt-4 space-y-3 text-sm text-slate-700">
        <li><strong class="text-[#101E33]">Be welcoming.</strong> New members and beginners deserve the same respect as experienced professionals.</li>
        <li><strong class="text-[#101E33]">Share knowledge.</strong> Answer questions, give constructive feedback and credit other people's work.</li>
        <li><strong class="text-[#101E33]">Stay professional.</strong> Keep conversations respectful and focused on helping the community grow.</li>
        <li><strong class="text-[#101E33]">Respect privacy.</strong> Do not share another member's personal information without permission.</li>
      </ul>
      <a href="{{ route('community_guidelines') }}" class="mt-6 inline-block text-[#FF5C3D] font-medium hover:underline {{ $focus }} rounded">Read the full community guidelines</a>
    </div>

    <div class="rounded-2xl bg-[#101E33] text-white p-8">
      <h2 class="text-xl font-bold">Everyone is welcome</h2>
      <p class="mt-3 text-slate-300 text-sm">MWTT is an inclusive, non-profit community. You do not need a job title or years of experience to belong, just an interest in technology and a willingness to learn and contribute.</p>
      <div class="mt-6 flex flex-wrap gap-3">
        @guest
          <a href="{{ route('register') }}" class="px-5 py-2.5 bg-[#FF5C3D] rounded-lg font-semibold hover:opacity-90 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Join MWTT</a>
        @endguest
        <a href="{{ route('contact') }}" class="px-5 py-2.5 border border-white/20 rounded-lg font-semibold hover:bg-white/10 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Contact us</a>
      </div>
    </div>
  </div>
</section>
@endsection