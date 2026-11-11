@extends('layouts.app')
@section('content')

<!-- Hero -->
<section class="bg-[#101E33] text-white">
  <div class="max-w-7xl mx-auto px-4 py-20 grid md:grid-cols-2 gap-12 items-center">
    <div>
      <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-white/10 border border-white/20">Non-profit tech community · Nigeria</span>
      <h1 class="mt-5 text-4xl md:text-6xl font-bold leading-tight tracking-tight">Where Techies Connect, Collaborate and Grow.</h1>
      <p class="mt-5 text-lg text-slate-300 max-w-xl">Meet with the Techies is a growing technology community connecting professionals, developers, innovators, founders, students and technology hubs to build a stronger, more connected tech ecosystem.</p>
      <div class="mt-8 flex flex-wrap gap-3">
        <a href="{{ route('register') }}" class="px-6 py-3 bg-[#FF5C3D] text-white font-semibold rounded-lg shadow-lg hover:opacity-90 transition">Join the Community</a>
        <a href="/members" class="px-6 py-3 border border-white/30 rounded-lg hover:bg-white/10 transition">Explore Our Network</a>
      </div>
      <div class="mt-10 flex items-center gap-6 text-sm text-slate-400">
        <div><span class="text-white font-bold text-2xl">{{ $stats['members'] }}</span><br>Techies</div>
        <div><span class="text-white font-bold text-2xl">{{ $stats['hubs'] }}</span><br>Verified Hubs</div>
        <div><span class="text-white font-bold text-2xl">{{ $stats['chapters'] }}</span><br>Chapters</div>
      </div>
    </div>
    <div class="hidden md:flex justify-center">
      <svg viewBox="0 0 400 400" class="w-full max-w-md" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="200" cy="200" r="180" stroke="#FF5C3D" stroke-width="1" stroke-dasharray="4 6" opacity="0.4"/>
        <circle cx="200" cy="200" r="120" stroke="#06B6D4" stroke-width="1" stroke-dasharray="4 6" opacity="0.4"/>
        @foreach([[100,120],[280,100],[320,260],[120,300],[60,200],[260,320]] as $pt)
        <circle cx="{{ $pt[0] }}" cy="{{ $pt[1] }}" r="14" fill="#FF5C3D"/>
        @endforeach
        <circle cx="200" cy="200" r="22" fill="#06B6D4"/>
        <line x1="100" y1="120" x2="200" y2="200" stroke="#ffffff33" stroke-width="1"/>
        <line x1="280" y1="100" x2="200" y2="200" stroke="#ffffff33" stroke-width="1"/>
        <line x1="320" y1="260" x2="200" y2="200" stroke="#ffffff33" stroke-width="1"/>
        <line x1="120" y1="300" x2="200" y2="200" stroke="#ffffff33" stroke-width="1"/>
        <line x1="60" y1="200" x2="200" y2="200" stroke="#ffffff33" stroke-width="1"/>
        <line x1="260" y1="320" x2="200" y2="200" stroke="#ffffff33" stroke-width="1"/>
      </svg>
    </div>
  </div>
</section>

<!-- Stats -->
<section class="max-w-7xl mx-auto px-4 -mt-10 relative z-10">
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach (['members'=>'Registered Techies','hubs'=>'Verified Hubs','chapters'=>'Chapters','events'=>'Events Organized'] as $k=>$label)
      <div class="bg-white rounded-2xl p-6 text-center shadow-xl border border-slate-100">
        <div class="text-3xl font-bold text-[#FF5C3D]">{{ $stats[$k] }}</div>
        <div class="text-sm text-slate-500 mt-1">{{ $label }}</div>
      </div>
    @endforeach
  </div>
</section>

<!-- About -->
<section class="max-w-7xl mx-auto px-4 py-20">
  <div class="max-w-3xl">
    <h2 class="text-3xl md:text-4xl font-bold text-[#101E33]">More Than a Meetup. A Connected Tech Community.</h2>
    <p class="mt-4 text-slate-600 text-lg">MWTT brings together developers, designers, engineers, founders, students and technology hubs to collaborate, learn and grow — connecting the entire technology ecosystem under one network.</p>
    <a href="/about" class="inline-block mt-6 text-[#FF5C3D] font-semibold hover:underline">Learn About Us →</a>
  </div>
</section>

<!-- Who we bring together -->
<section class="bg-white py-20">
  <div class="max-w-7xl mx-auto px-4">
    <h2 class="text-3xl font-bold text-[#101E33] mb-8">Who We Bring Together</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
      @foreach ([
        ['Developers','Building software and products that move the world.'],
        ['Designers','Crafting beautiful, usable digital experiences.'],
        ['Engineers','Solving hard technical problems at scale.'],
        ['Founders','Creating companies and communities.'],
        ['Product Managers','Turning ideas into shipped products.'],
        ['Tech Students','Learning and launching their careers.'],
        ['Tech Hubs','Running spaces where innovation happens.'],
        ['Innovation Communities','Growing ecosystems across regions.'],
      ] as $card)
      <div class="rounded-2xl border border-slate-100 p-5 hover:shadow-md transition">
        <div class="w-10 h-10 rounded-lg bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center font-bold">{{ substr($card[0],0,1) }}</div>
        <h3 class="mt-3 font-semibold text-[#101E33]">{{ $card[0] }}</h3>
        <p class="mt-1 text-sm text-slate-500">{{ $card[1] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- Members -->
<section class="max-w-7xl mx-auto px-4 py-20">
  <div class="flex items-end justify-between mb-8">
    <h2 class="text-3xl font-bold text-[#101E33]">Explore the Community</h2>
    <a href="/members" class="text-[#FF5C3D] font-medium">View all →</a>
  </div>
  <div class="grid md:grid-cols-3 gap-4">
    @forelse ($members as $m)
      <a href="/members/{{ $m->profile?->username ?? $m->id }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition flex gap-4 items-center">
        <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold">{{ strtoupper(substr($m->name,0,1)) }}</div>
        <div class="min-w-0">
          <div class="font-semibold text-[#101E33] truncate">{{ $m->name }}</div>
          <div class="text-sm text-slate-500 truncate">{{ $m->profile?->professional_title ?? 'Member' }}</div>
          <div class="text-xs text-slate-400 truncate">{{ $m->profile?->city }}{{ $m->profile?->state ? ', '.$m->profile?->state : '' }}</div>
        </div>
      </a>
    @empty
      <p class="text-slate-500 col-span-3">No public members yet. Be the first to join.</p>
    @endforelse
  </div>
</section>

<!-- Hubs -->
<section class="bg-[#F8FAFC] py-20">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex items-end justify-between mb-8">
      <h2 class="text-3xl font-bold text-[#101E33]">Discover Technology Hubs</h2>
      <a href="/hubs" class="text-[#FF5C3D] font-medium">View all →</a>
    </div>
    <div class="grid md:grid-cols-3 gap-4">
      @forelse ($hubs as $h)
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center font-bold text-slate-500">{{ strtoupper(substr($h->name,0,1)) }}</div>
            <div>
              <div class="font-semibold text-[#101E33]">{{ $h->name }}</div>
              <div class="text-xs text-slate-500">{{ $h->city }}{{ $h->state ? ', '.$h->state : '' }}</div>
            </div>
            @if($h->is_verified)<span class="ml-auto text-xs px-2 py-1 rounded-full bg-green-50 text-green-600 font-medium">Verified</span>@endif
          </div>
          <p class="mt-4 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($h->description,110) }}</p>
          <a href="/hubs/{{ $h->slug }}" class="inline-block mt-4 text-[#FF5C3D] text-sm font-medium">View Hub →</a>
        </div>
      @empty
        <p class="text-slate-500 col-span-3">No verified hubs yet.</p>
      @endforelse
    </div>
  </div>
</section>

<!-- Events -->
<section class="max-w-7xl mx-auto px-4 py-20">
  <div class="flex items-end justify-between mb-8">
    <h2 class="text-3xl font-bold text-[#101E33]">Upcoming Events</h2>
    <a href="/events" class="text-[#FF5C3D] font-medium">View all →</a>
  </div>
  <div class="grid md:grid-cols-3 gap-4">
    @forelse ($events as $e)
      <div class="bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-md transition">
        <div class="h-32 bg-slate-100"></div>
        <div class="p-5">
          <div class="text-xs text-[#FF5C3D] font-medium">{{ $e->start_date }} @if($e->is_online)· Online @else· {{ $e->city }} @endif</div>
          <h3 class="mt-2 font-semibold text-[#101E33]">{{ $e->title }}</h3>
          <a href="/events/{{ $e->slug }}" class="inline-block mt-3 text-sm text-[#FF5C3D] font-medium">Register →</a>
        </div>
      </div>
    @empty
      <p class="text-slate-500 col-span-3">No upcoming events.</p>
    @endforelse
  </div>
</section>

<!-- Opportunities -->
<section class="bg-[#F8FAFC] py-20">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex items-end justify-between mb-8">
      <h2 class="text-3xl font-bold text-[#101E33]">Community Opportunities</h2>
      <a href="/opportunities" class="text-[#FF5C3D] font-medium">View all →</a>
    </div>
    <div class="grid md:grid-cols-3 gap-4">
      @forelse ($opportunities as $o)
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition">
          <span class="text-xs px-2 py-1 rounded-full bg-[#FF5C3D]/10 text-[#FF5C3D] font-medium">{{ $o->category?->name ?? 'Opportunity' }}</span>
          <h3 class="mt-3 font-semibold text-[#101E33]">{{ $o->title }}</h3>
          <p class="mt-1 text-sm text-slate-500">{{ $o->organization }} · {{ $o->location ?? ($o->is_remote ? 'Remote' : '') }}</p>
          <a href="/opportunities/{{ $o->slug }}" class="inline-block mt-4 text-[#FF5C3D] text-sm font-medium">View →</a>
        </div>
      @empty
        <p class="text-slate-500 col-span-3">No opportunities yet.</p>
      @endforelse
    </div>
  </div>
</section>

<!-- Hub partner CTA -->
<section class="max-w-7xl mx-auto px-4 py-20">
  <div class="bg-[#101E33] text-white rounded-3xl p-10 md:p-16 grid md:grid-cols-2 gap-8 items-center">
    <div>
      <h2 class="text-3xl md:text-4xl font-bold">Bring Your Tech Community Into the Network.</h2>
      <p class="mt-4 text-slate-300">Are you running a technology hub, innovation centre, developer community or university tech community? Join MWTT and connect with a wider technology ecosystem.</p>
      <a href="/hubs/register" class="inline-block mt-6 px-6 py-3 bg-[#FF5C3D] rounded-lg font-semibold hover:opacity-90 transition">Register Your Hub</a>
    </div>
    <div class="hidden md:block text-slate-400 text-sm">
      <ul class="space-y-2">
        <li>✓ Dedicated hub profile & public page</li>
        <li>✓ Publish events to the MWTT directory</li>
        <li>✓ Manage your team & community members</li>
        <li>✓ Showcase programs and opportunities</li>
      </ul>
    </div>
  </div>
</section>

<!-- Chapters -->
<section class="max-w-7xl mx-auto px-4 py-20">
  <h2 class="text-3xl font-bold text-[#101E33] mb-6">Community Chapters</h2>
  <div class="flex flex-wrap gap-3">
    @forelse ($chapters as $c)
      <a href="/chapters/{{ $c->slug }}" class="px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:border-[#FF5C3D] hover:text-[#FF5C3D] transition">{{ $c->name }}</a>
    @empty
      <p class="text-slate-500">No chapters yet.</p>
    @endforelse
  </div>
</section>

<!-- Newsletter -->
<section class="bg-[#F8FAFC] py-20">
  <div class="max-w-2xl mx-auto px-4 text-center">
    <h2 class="text-3xl font-bold text-[#101E33]">Stay in the Loop</h2>
    <p class="mt-3 text-slate-600">Subscribe to community updates, events and opportunities.</p>
    <form method="POST" action="/newsletter" class="mt-6 flex flex-col sm:flex-row gap-2">
      @csrf
      <input name="name" placeholder="Your name" class="flex-1 border border-slate-200 rounded-lg px-4 py-3" required>
      <input name="email" type="email" placeholder="Your email" class="flex-1 border border-slate-200 rounded-lg px-4 py-3" required>
      <button class="px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Subscribe</button>
    </form>
  </div>
</section>

<!-- Final CTA -->
<section class="max-w-7xl mx-auto px-4 py-20 text-center">
  <h2 class="text-4xl font-bold text-[#101E33]">The Future of Tech Is Better Together.</h2>
  <div class="mt-8 flex justify-center gap-3">
    <a href="{{ route('register') }}" class="px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Join the Community</a>
    <a href="/hubs/register" class="px-6 py-3 border border-slate-300 rounded-lg hover:bg-slate-50 transition">Register Your Hub</a>
  </div>
</section>
@endsection
