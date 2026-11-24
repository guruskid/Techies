@extends('layouts.app')
@section('content')
@php $c = $chapter; @endphp
<section class="bg-[#101E33] text-white py-16 relative overflow-hidden">
  <div class="absolute inset-0 opacity-20">
    <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-[#FF5C3D] blur-3xl"></div>
  </div>
  <div class="max-w-7xl mx-auto px-4 relative flex flex-col md:flex-row items-center gap-6">
    @if($c->logo)
      <img src="{{ $c->logo }}" class="w-24 h-24 rounded-2xl object-cover border-4 border-white/20" alt="{{ $c->name }}">
    @else
      <div class="w-24 h-24 rounded-2xl bg-white/10 border-4 border-white/20 flex items-center justify-center text-3xl font-bold">{{ strtoupper(substr($c->name,0,1)) }}</div>
    @endif
    <div>
      <h1 class="text-3xl font-bold">{{ $c->name }}</h1>
      <p class="text-slate-300 mt-1">{{ collect([$c->state, $c->country])->filter()->implode(', ') ?: 'Chapter' }}</p>
      <span class="inline-block mt-2 text-xs px-2 py-1 rounded-full bg-green-500/20 text-green-300">{{ ucfirst($c->status) }}</span>
    </div>
  </div>
</section>
<div class="max-w-7xl mx-auto px-4 py-12 grid md:grid-cols-3 gap-8">
  <div class="md:col-span-2 space-y-6">
    <div class="bg-white rounded-2xl p-8 border border-slate-100 shadow-sm">
      <h2 class="text-2xl font-bold text-[#101E33] mb-3">About this Chapter</h2>
      @if($c->description)
        <div class="text-slate-600 leading-relaxed text-lg article-body">{!! \App\Support\RichText::render($c->description) !!}</div>
      @else
        <p class="text-slate-500">No description provided.</p>
      @endif
    </div>
    <div class="bg-white rounded-2xl p-8 border border-slate-100 shadow-sm">
      <h2 class="text-2xl font-bold text-[#101E33] mb-3">Recent Events</h2>
      @forelse($events as $event)
        <a href="{{ route('events.show', $event->slug) }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-50 transition">
          <div class="w-12 h-12 rounded-xl bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center font-bold">{{ strtoupper(substr($event->title, 0, 1)) }}</div>
          <div class="flex-1 min-w-0">
            <div class="font-medium text-[#101E33] truncate">{{ $event->title }}</div>
            <div class="text-xs text-slate-500">{{ $event->start_date->format('M j, Y') }} @if($event->is_online) · Online @elseif($event->city) · {{ $event->city }} @endif</div>
          </div>
        </a>
      @empty
        <p class="text-slate-500 text-sm">No upcoming events for this chapter yet.</p>
      @endforelse
    </div>
  </div>
  <aside class="space-y-6">
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm sticky top-24">
      <h3 class="text-lg font-bold text-[#101E33] mb-4">Chapter Info</h3>
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
          <div class="text-slate-500">State</div>
          <div class="font-medium text-[#101E33] mt-1">{{ $c->state ?? '—' }}</div>
        </div>
        <div>
          <div class="text-slate-500">Country</div>
          <div class="font-medium text-[#101E33] mt-1">{{ $c->country ?? '—' }}</div>
        </div>
        <div>
          <div class="text-slate-500">Coordinator</div>
          <div class="font-medium text-[#101E33] mt-1">{{ $c->coordinator?->name ?? '—' }}</div>
        </div>
        <div>
          <div class="text-slate-500">Members</div>
          <div class="font-medium text-[#101E33] mt-1">{{ $c->members->count() }}</div>
        </div>
        <div class="col-span-2">
          <div class="text-slate-500">Status</div>
          <div class="font-medium text-[#101E33] mt-1">{{ ucfirst($c->status) }}</div>
        </div>
      </div>
      <div class="mt-6 space-y-2">
        @auth
          @if($isMember)
            <div class="p-3 rounded-lg bg-green-50 text-green-700 text-sm text-center">You are a member of this chapter.</div>
            <form method="POST" action="{{ route('chapters.leave', $c) }}">
              @csrf
              <button class="w-full px-6 py-3 border border-slate-200 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition">Leave chapter</button>
            </form>
          @else
            <form method="POST" action="{{ route('chapters.join', $c) }}">
              @csrf
              <button class="w-full px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Join this chapter</button>
            </form>
          @endif
        @else
          <a href="{{ route('login') }}" class="block w-full text-center px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Log in to join</a>
        @endauth
        <a href="/chapters" class="block w-full text-center px-6 py-3 border border-slate-200 rounded-lg font-medium hover:bg-slate-50 transition">Back to Chapters</a>
      </div>
    </div>
  </aside>
</div>
@endsection
