@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

  <!-- Welcome -->
  <div class="bg-[#101E33] text-white rounded-3xl p-8 mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl md:text-3xl font-bold">Welcome back, {{ auth()->user()->name }} 👋</h1>
      <p class="text-slate-300 mt-1">Here's what's happening in your community.</p>
    </div>
    <a href="/members/{{ auth()->user()->profile?->username ?? auth()->user()->id }}" class="px-5 py-2.5 bg-[#FF5C3D] rounded-lg font-semibold hover:opacity-90 transition">View My Profile</a>
  </div>

  <!-- Stats row -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <a href="/profile" class="block bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition">
      <div class="text-sm text-slate-500">Profile Completion</div>
      <div class="mt-2 text-2xl font-bold text-[#FF5C3D]">{{ $completion }}%</div>
      <div class="mt-2 h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-[#FF5C3D]" style="width: {{ $completion }}%"></div></div>
      <div class="mt-2 text-xs text-[#FF5C3D] font-medium">Complete profile →</div>
    </a>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
      <div class="text-sm text-slate-500">Membership Status</div>
      <div class="mt-2 text-2xl font-bold text-[#101E33]">{{ ucfirst(auth()->user()->status) }}</div>
      @if(auth()->user()->status === 'active')
        <div class="mt-2 text-xs text-slate-400">Verified member</div>
      @endif
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
      <div class="text-sm text-slate-500">Upcoming Events</div>
      <div class="mt-2 text-2xl font-bold text-[#101E33]">{{ $upcomingEvents->count() }}</div>
      <div class="mt-2 text-xs text-slate-400">In the next weeks</div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
      <div class="text-sm text-slate-500">Saved Items</div>
      <div class="mt-2 text-2xl font-bold text-[#101E33]">0</div>
      <div class="mt-2 text-xs text-slate-400">Bookmarks</div>
    </div>
  </div>

  <!-- Events + Hubs -->
  <div class="grid md:grid-cols-3 gap-6">
    <!-- Upcoming Events -->
    <div class="md:col-span-2 bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-[#101E33]">Upcoming Events</h2>
        <a href="/events" class="text-sm text-[#FF5C3D] hover:underline">View all</a>
      </div>
      <div class="space-y-3">
        @forelse($upcomingEvents as $e)
          <a href="/events/{{ $e->slug }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-50 transition">
            <div class="w-12 h-12 rounded-xl bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center font-bold">{{ strtoupper(substr($e->title,0,1)) }}</div>
            <div class="flex-1 min-w-0">
              <div class="font-medium text-[#101E33] truncate">{{ $e->title }}</div>
              <div class="text-xs text-slate-500">
                {{ \Illuminate\Support\Carbon::parse($e->start_date)->format('M j, Y') }}
                @if($e->is_online)
                  · Online
                @elseif($e->city)
                  · {{ $e->city }}
                @endif
              </div>
            </div>
            <span class="text-xs text-[#FF5C3D] font-medium">Register →</span>
          </a>
        @empty
          <p class="text-slate-500 text-sm">No upcoming events.</p>
        @endforelse
      </div>
    </div>

    <!-- Recommended Hubs -->
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-[#101E33]">Recommended Hubs</h2>
        <a href="/hubs" class="text-sm text-[#FF5C3D] hover:underline">View all</a>
      </div>
      <div class="space-y-3">
        @forelse($recommendedHubs as $h)
          <a href="/hubs/{{ $h->slug }}" class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition">
            @if($h->logo)
              <img src="{{ $h->logo }}" class="w-10 h-10 rounded-lg object-cover" alt="{{ $h->name }}">
            @else
              <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center font-bold">{{ strtoupper(substr($h->name,0,1)) }}</div>
            @endif
            <div class="flex-1 min-w-0">
              <div class="font-medium text-[#101E33] text-sm truncate">{{ $h->name }}</div>
              <div class="text-xs text-slate-500 truncate">{{ $h->city }}</div>
            </div>
          </a>
        @empty
          <p class="text-slate-500 text-sm">No hubs yet.</p>
        @endforelse
      </div>
    </div>
  </div>

  <!-- Opportunities + Announcements -->
  <div class="grid md:grid-cols-3 gap-6 mt-6">
    <!-- Opportunities -->
    <div class="md:col-span-2 bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-[#101E33]">Recommended Opportunities</h2>
        <a href="/opportunities" class="text-sm text-[#FF5C3D] hover:underline">View all</a>
      </div>
      <div class="space-y-3">
        @forelse($opportunities as $o)
          <a href="/opportunities/{{ $o->slug }}" class="block p-3 rounded-xl hover:bg-slate-50 transition border border-slate-100">
            <div class="font-medium text-[#101E33]">{{ $o->title }}</div>
            <div class="text-xs text-slate-500 mt-1">{{ $o->organization }} · {{ $o->location ?? ($o->is_remote ? 'Remote' : '') }}</div>
          </a>
        @empty
          <p class="text-slate-500 text-sm">No opportunities yet.</p>
        @endforelse
      </div>
    </div>

    <!-- Announcements -->
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h2 class="text-lg font-bold text-[#101E33] mb-4">Announcements</h2>
      <div class="space-y-3">
        @forelse($announcements as $a)
          <div class="p-3 rounded-xl bg-slate-50">
            <div class="font-medium text-[#101E33] text-sm">{{ $a->title }}</div>
            <div class="text-xs text-slate-500 mt-1">{{ \Illuminate\Support\Str::limit($a->body, 80) }}</div>
          </div>
        @empty
          <p class="text-slate-500 text-sm">No announcements yet.</p>
        @endforelse
      </div>
    </div>
  </div>

  <!-- Recently Joined -->
  <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm mt-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-bold text-[#101E33]">Recently Joined Techies</h2>
      <a href="/members" class="text-sm text-[#FF5C3D] hover:underline">View all</a>
    </div>
    <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
      @forelse($recentMembers as $m)
        <a href="/members/{{ $m->profile?->username ?? $m->id }}" class="flex items-center gap-2 p-2 rounded-xl hover:bg-slate-50 transition">
          <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center text-sm font-bold">{{ strtoupper(substr($m->name,0,1)) }}</div>
          <div class="min-w-0">
            <div class="text-sm font-medium text-[#101E33] truncate">{{ $m->name }}</div>
          </div>
        </a>
      @empty
        <p class="text-slate-500 text-sm col-span-full">No members yet.</p>
      @endforelse
    </div>
  </div>

</div>
@endsection