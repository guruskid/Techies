@extends('layouts.app')
@section('content')
@include('partials.member-nav')
@php
  $card = 'bg-white rounded-2xl border border-slate-100 shadow-sm';
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
  $link = 'text-[#FF5C3D] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $btn = 'inline-flex items-center justify-center px-5 py-2.5 rounded-lg text-sm font-semibold border transition '.$focus;

  $hub = $hub ?? null;
  $roleBadge = [
    'owner' => 'bg-[#101E33] text-white',
    'admin' => 'bg-[#FF5C3D]/10 text-[#C23A1F]',
  ];
@endphp

@if(! $hub)
  {{-- Instead of an "Undefined variable $hub" error when this account has no hub --}}
  <div class="max-w-2xl mx-auto px-4 py-16">
    <div class="{{ $card }} p-8 sm:p-10 text-center">
      <h1 class="text-2xl font-bold text-[#101E33]">You do not manage a hub yet</h1>
      <p class="mt-2 text-slate-600">Register your hub to build a team and manage it here.</p>
      <a href="/hubs/register" class="{{ $btn }} mt-6 border-[#FF5C3D] bg-[#FF5C3D] text-white hover:opacity-90">Register your hub</a>
    </div>
  </div>
@else
  <div class="max-w-4xl mx-auto px-4 py-8 pb-12">
    <a href="{{ route('hub.dashboard') }}" class="text-sm {{ $link }}">Back to hub dashboard</a>

    <div class="mt-4 mb-6">
      <h1 class="text-2xl font-bold text-[#101E33]">Team members</h1>
      <p class="text-slate-600 mt-1">{{ $hub->name }}</p>
    </div>

    <section class="{{ $card }} p-6" aria-labelledby="team-h">
      <h2 id="team-h" class="text-lg font-bold text-[#101E33] mb-4">Members</h2>

      @if($hub->teamMembers->isNotEmpty())
        <ul class="divide-y divide-slate-100">
          @foreach($hub->teamMembers as $member)
            @php
              $u = $member->user;
              $p = $u?->profile;
              $role = strtolower((string) $member->role);
            @endphp
            <li class="py-4 flex items-center justify-between gap-4">
              <div class="flex items-center gap-3 min-w-0">
                @if($p?->avatar)
                  <img src="{{ $p->avatar }}" alt="" loading="lazy" class="w-10 h-10 shrink-0 rounded-full object-cover">
                @else
                  <div class="w-10 h-10 shrink-0 rounded-full bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center font-bold" aria-hidden="true">{{ strtoupper(substr($u?->name ?? '?', 0, 1)) }}</div>
                @endif
                <div class="min-w-0">
                  <div class="font-medium text-[#101E33] truncate">
                    @if($p?->username && $p->is_public)
                      <a href="{{ $p->publicUrl() }}" class="hover:text-[#FF5C3D] hover:underline {{ $focus }} rounded">{{ $u->name }}</a>
                    @else
                      {{ $u?->name ?? 'Removed user' }}
                    @endif
                  </div>
                  @if($u?->email)
                    <div class="text-xs text-slate-600 break-all"><a href="mailto:{{ $u->email }}" class="hover:underline {{ $focus }} rounded">{{ $u->email }}</a></div>
                  @endif
                  @if($p?->professional_title)
                    <div class="text-xs text-slate-600">{{ $p->professional_title }}</div>
                  @endif
                </div>
              </div>

              <div class="shrink-0 text-right">
                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $roleBadge[$role] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($member->role) }}</span>
                @if($member->created_at)
                  <div class="mt-1 text-[11px] text-slate-500">Added {{ $member->created_at->format('M j, Y') }}</div>
                @endif
              </div>
            </li>
          @endforeach
        </ul>
      @else
        <div class="text-center py-6">
          <p class="font-medium text-[#101E33]">No team members yet</p>
          <p class="mt-1 text-sm text-slate-600">People you add to your hub team will appear here.</p>
        </div>
      @endif
    </section>
  </div>
@endif
@endsection