@extends('layouts.app')
@section('content')
@php $e = $event; @endphp
<section class="bg-[#101E33] text-white py-16 relative overflow-hidden">
  <div class="absolute inset-0 opacity-20">
    <div class="absolute -bottom-20 -left-20 w-72 h-72 rounded-full bg-[#FF5C3D] blur-3xl"></div>
  </div>
  <div class="max-w-7xl mx-auto px-4 relative">
    @if($e->category)<span class="inline-block text-xs px-3 py-1 rounded-full bg-white/10 border border-white/20">{{ $e->category->name }}</span>@endif
    <h1 class="mt-4 text-3xl md:text-4xl font-bold leading-tight">{{ $e->title }}</h1>
    <p class="mt-2 text-slate-300 text-lg">{{ $e->start_date->format('M j, Y') }} @if($e->start_time)· {{ $e->start_time }} @endif @if($e->is_online)· Online @else· {{ $e->city }} @endif</p>
    <div class="mt-4 flex flex-wrap gap-2">
      @if($e->is_online)<span class="text-xs px-3 py-1 rounded-full bg-cyan-500/20 text-cyan-300">Online Event</span>@else<span class="text-xs px-3 py-1 rounded-full bg-white/10">In Person</span>@endif
      @if($e->registration_type === 'free')<span class="text-xs px-3 py-1 rounded-full bg-green-500/20 text-green-300">Free</span>@else<span class="text-xs px-3 py-1 rounded-full bg-[#FF5C3D]/20 text-[#FF5C3D]">Paid</span>@endif
    </div>
  </div>
</section>

@if($e->cover_image)
<div class="max-w-7xl mx-auto px-4 -mt-10 relative z-10">
  <img src="{{ $e->cover_image }}" alt="{{ $e->title }}" class="w-full h-64 md:h-80 object-cover rounded-2xl shadow-lg border border-white/20">
</div>
@endif

<div class="max-w-7xl mx-auto px-4 py-12 grid md:grid-cols-3 gap-8">
  <div class="md:col-span-2 space-y-6">
    <div class="bg-white rounded-2xl p-8 border border-slate-100 shadow-sm">
      <h2 class="text-2xl font-bold text-[#101E33] mb-3">About this Event</h2>
      <p class="text-slate-600 leading-relaxed text-lg">{{ $e->description ?? 'No description provided.' }}</p>
    </div>

    @if($e->host || $e->key_speakers)
    <div class="bg-white rounded-2xl p-8 border border-slate-100 shadow-sm">
      <h2 class="text-2xl font-bold text-[#101E33] mb-3">Hosted by</h2>
      @if($e->host)<p class="text-slate-600 text-lg">{{ $e->host }}</p>@endif
      @if($e->key_speakers)
        <div class="mt-4">
          <h3 class="font-semibold text-[#101E33] mb-2">Key speakers</h3>
          <div class="text-slate-600 whitespace-pre-line">{{ $e->key_speakers }}</div>
        </div>
      @endif
    </div>
    @endif

    @if($e->agenda)
    <div class="bg-white rounded-2xl p-8 border border-slate-100 shadow-sm">
      <h2 class="text-2xl font-bold text-[#101E33] mb-3">Agenda</h2>
      <div class="text-slate-600 leading-relaxed whitespace-pre-line">{{ $e->agenda }}</div>
    </div>
    @endif

    @if($e->requirements)
    <div class="bg-white rounded-2xl p-8 border border-slate-100 shadow-sm">
      <h2 class="text-2xl font-bold text-[#101E33] mb-3">Requirements</h2>
      <div class="text-slate-600 leading-relaxed whitespace-pre-line">{{ $e->requirements }}</div>
    </div>
    @endif

    @if($e->contact_email || $e->contact_phone)
    <div class="bg-white rounded-2xl p-8 border border-slate-100 shadow-sm">
      <h2 class="text-2xl font-bold text-[#101E33] mb-3">Contact</h2>
      <div class="space-y-1 text-slate-600">
        @if($e->contact_email)<p>Email: <a href="mailto:{{ $e->contact_email }}" class="text-[#FF5C3D] hover:underline">{{ $e->contact_email }}</a></p>@endif
        @if($e->contact_phone)<p>Phone: {{ $e->contact_phone }}</p>@endif
      </div>
    </div>
    @endif

    @if($e->hub || $e->chapter)
    <div class="bg-white rounded-2xl p-8 border border-slate-100 shadow-sm">
      <h2 class="text-2xl font-bold text-[#101E33] mb-3">Organized by</h2>
      <div class="flex flex-wrap gap-4">
        @if($e->hub)
          <a href="/hubs/{{ $e->hub->slug }}" class="flex items-center gap-3 hover:opacity-80 transition">
            @if($e->hub->logo)<img src="{{ $e->hub->logo }}" class="w-12 h-12 rounded-xl object-cover" alt="{{ $e->hub->name }}">@else<div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center font-bold">{{ strtoupper(substr($e->hub->name,0,1)) }}</div>@endif
            <div><div class="font-semibold text-[#101E33]">{{ $e->hub->name }}</div><div class="text-xs text-slate-500">Technology Hub</div></div>
          </a>
        @endif
        @if($e->chapter)
          <a href="/chapters/{{ $e->chapter->slug }}" class="flex items-center gap-3 hover:opacity-80 transition">
            <div class="w-12 h-12 rounded-xl bg-[#101E33] text-white flex items-center justify-center font-bold">{{ strtoupper(substr($e->chapter->name,0,1)) }}</div>
            <div><div class="font-semibold text-[#101E33]">{{ $e->chapter->name }}</div><div class="text-xs text-slate-500">Chapter</div></div>
          </a>
        @endif
        @if($e->organizer)
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-500">{{ strtoupper(substr($e->organizer->name,0,1)) }}</div>
            <div><div class="font-semibold text-[#101E33]">{{ $e->organizer->name }}</div><div class="text-xs text-slate-500">Organizer</div></div>
          </div>
        @endif
      </div>
    </div>
    @endif

    @if($e->meeting_url)
    <div class="bg-white rounded-2xl p-8 border border-slate-100 shadow-sm">
      <h2 class="text-2xl font-bold text-[#101E33] mb-2">Join Online</h2>
      <a href="{{ $e->meeting_url }}" target="_blank" class="text-[#FF5C3D] hover:underline break-all">{{ $e->meeting_url }}</a>
    </div>
    @endif
  </div>

  <aside class="space-y-6">
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm sticky top-24">
      <h3 class="text-lg font-bold text-[#101E33] mb-4">Event Details</h3>
      <dl class="space-y-4 text-sm">
        <div class="flex justify-between gap-3"><dt class="text-slate-500">Date</dt><dd class="font-medium text-[#101E33] text-right">{{ $e->start_date->format('M j, Y') }}@if($e->end_date && !$e->end_date->equalTo($e->start_date)) – {{ $e->end_date->format('M j, Y') }}@endif</dd></div>
        @if($e->start_time)<div class="flex justify-between gap-3"><dt class="text-slate-500">Time</dt><dd class="font-medium text-[#101E33] text-right">{{ $e->start_time }}@if($e->end_time) – {{ $e->end_time }}@endif</dd></div>@endif
        <div class="flex justify-between gap-3"><dt class="text-slate-500">Venue</dt><dd class="font-medium text-[#101E33] text-right">{{ $e->venue ?? ($e->is_online ? 'Online' : '—') }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-slate-500">Location</dt><dd class="font-medium text-[#101E33] text-right">{{ $e->city ?? '—' }}{{ $e->state ? ', '.$e->state : '' }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-slate-500">Type</dt><dd class="font-medium text-[#101E33] text-right">{{ $e->is_online ? 'Online' : 'In person' }}</dd></div>
        @if($e->host)<div class="flex justify-between gap-3"><dt class="text-slate-500">Host</dt><dd class="font-medium text-[#101E33] text-right">{{ $e->host }}</dd></div>@endif
        @if($e->organizer)<div class="flex justify-between gap-3"><dt class="text-slate-500">Organizer</dt><dd class="font-medium text-[#101E33] text-right">{{ $e->organizer->name }}</dd></div>@endif
        <div class="flex justify-between gap-3"><dt class="text-slate-500">Registration</dt><dd class="font-medium text-[#101E33] text-right">{{ ucfirst($e->registration_type) }}</dd></div>
        @if($e->fee)<div class="flex justify-between gap-3"><dt class="text-slate-500">Fee</dt><dd class="font-medium text-[#101E33] text-right">₦{{ number_format($e->fee,2) }}</dd></div>@endif
        <div class="flex justify-between gap-3"><dt class="text-slate-500">Max Attendees</dt><dd class="font-medium text-[#101E33] text-right">{{ $e->max_attendees ?? 'Unlimited' }}</dd></div>
        @if($e->registration_deadline)<div class="flex justify-between gap-3"><dt class="text-slate-500">Reg. Deadline</dt><dd class="font-medium text-[#101E33] text-right">{{ $e->registration_deadline->format('M j, Y') }}</dd></div>@endif
      </dl>
      <div class="mt-6 space-y-2">
        @php
          $isRegistered = $registration && $registration->status === 'registered';
          $isWaitlisted = $registration && $registration->status === 'waitlisted';
          $isClosed = $e->registration_deadline && $e->registration_deadline->isPast();
          $isFull = $spotsLeft !== null && $spotsLeft === 0;
        @endphp

        @if($isRegistered)
          <div class="p-3 rounded-lg bg-green-50 text-green-700 text-sm text-center">You are registered for this event.</div>
          <form method="POST" action="{{ route('events.cancel', $e) }}">
            @csrf
            <button class="w-full px-6 py-3 border border-red-200 text-red-600 rounded-lg font-semibold hover:bg-red-50 transition">Cancel Registration</button>
          </form>
        @elseif($isWaitlisted)
          <div class="p-3 rounded-lg bg-amber-50 text-amber-800 text-sm text-center">You are on the waitlist. We will email you if a spot opens.</div>
          <form method="POST" action="{{ route('events.cancel', $e) }}">
            @csrf
            <button class="w-full px-6 py-3 border border-slate-200 text-slate-700 rounded-lg font-semibold hover:bg-slate-50 transition">Leave Waitlist</button>
          </form>
        @elseif($isClosed)
          <button type="button" disabled class="block w-full text-center px-6 py-3 bg-slate-200 text-slate-500 rounded-lg font-semibold cursor-not-allowed">Registration Closed</button>
        @elseif($isFull && auth()->check())
          <form method="POST" action="{{ route('events.register', $e) }}">
            @csrf
            <button class="w-full px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Join the Waitlist</button>
          </form>
        @elseif($isFull)
          <button type="button" disabled class="block w-full text-center px-6 py-3 bg-slate-200 text-slate-500 rounded-lg font-semibold cursor-not-allowed">Event Fully Booked</button>
        @elseif(auth()->check())
          <form method="POST" action="{{ route('events.register', $e) }}">
            @csrf
            <button class="w-full px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Register for Event</button>
          </form>
        @else
          <a href="{{ route('login') }}" class="block w-full text-center px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Log in to Register</a>
        @endif

        @if($spotsLeft !== null)
          <p class="text-xs text-slate-500 text-center">{{ $spotsLeft }} {{\Illuminate\Support\Str::plural('spot', $spotsLeft)}} left</p>
        @endif
        <a href="{{ route('events.calendar', $e->slug) }}" class="block w-full text-center px-6 py-3 border border-[#101E33]/20 text-[#101E33] rounded-lg font-medium hover:bg-slate-50 transition">Add to calendar</a>
        <a href="/events" class="block w-full text-center px-6 py-3 border border-slate-200 rounded-lg font-medium hover:bg-slate-50 transition">Back to Events</a>
      </div>
    </div>
  </aside>
</div>
@endsection
