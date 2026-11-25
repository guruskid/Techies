<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Workspace · Meet with the Techies')</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
@include('partials.head-styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stack('head')
</head>
<body class="bg-[#F4F6F8] text-slate-800 antialiased">
@php
  $user = auth()->user();
  $super = $user?->isSuperAdmin();
  $workspaceDashboard = $super ? route('super-admin.dashboard') : route('admin.dashboard');
  $nav = [
    'Overview' => [
      ['Dashboard', $workspaceDashboard, $super ? request()->is('super-admin') : request()->is('admin', 'admin/dashboard')],
      ['Manage centre', route('admin.manage'), request()->is('admin/manage')],
    ],
    'People and places' => [
      ['Members', route('admin.members.index'), request()->is('admin/members*')],
      ['Hubs', route('admin.hubs.index'), request()->is('admin/hubs*')],
      ['Chapters', route('admin.chapters.index'), request()->is('admin/chapters*')],
    ],
    'Network activity' => [
      ['Mentorship', route('admin.mentorship.index'), request()->is('admin/mentorship*')],
      ['Cohorts', route('admin.cohorts.index'), request()->is('admin/cohorts*')],
      ['Referrals', route('admin.referrals.index'), request()->is('admin/referrals*')],
      ['Introductions', route('admin.introductions.index'), request()->is('admin/introductions*')],
    ],
    'Member programmes' => [
      ['Showcase', route('admin.showcase.index'), request()->is('admin/showcase*')],
      ['Lounge', route('admin.lounge.index'), request()->is('admin/lounge*')],
      ['Grants', route('admin.grants.index'), request()->is('admin/grants*')],
      ['Spotlights', route('admin.spotlights.index'), request()->is('admin/spotlights*')],
      ['Feedback', route('admin.feedback.index'), request()->is('admin/feedback*')],
      ['Certificates', route('admin.certificates.index'), request()->is('admin/certificates*')],
    ],
    'Content' => [
      ['Content review', route('admin.content.index'), request()->is('admin/content*')],
      ['Announcements', route('admin.announcements.index'), request()->is('admin/announcements*')],
      ['Events', route('admin.events.index'), request()->is('manage/events*')],
      ['Opportunities', route('admin.opportunities.index'), request()->is('manage/opportunities*')],
      ['Call for speakers', route('admin.speaker-calls.index'), request()->is('manage/speaker-calls*')],
      ['Benefits', route('admin.benefits.index'), request()->is('admin/benefits*')],
      ['Categories', route('admin.categories.index'), request()->is('admin/categories*', 'admin/event-categories*', 'admin/opportunity-categories*')],
    ],
    'Communication' => [
      ['Mail centre', route('admin.mail.index'), request()->is('admin/mail*')],
    ],
  ];
  if ($super) {
    $nav['Super admin'] = [
      ['Users and roles', route('super-admin.users.index'), request()->is('super-admin/users*')],
      ['Role catalogue', route('super-admin.roles.index'), request()->is('super-admin/roles*')],
      ['Settings', route('super-admin.settings.edit'), request()->is('super-admin/settings*')],
      ['Audit logs', route('super-admin.audit-logs.index'), request()->is('super-admin/audit-logs*')],
    ];
  }
@endphp

<div x-data="{ mobileNavOpen: false }">
<header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur">
  <div class="flex h-16 items-center justify-between gap-4 px-4 lg:px-6">
    <div class="flex items-center gap-3">
      <button type="button" @click="mobileNavOpen = !mobileNavOpen" :aria-expanded="mobileNavOpen.toString()" aria-controls="mobile-workspace-navigation" class="rounded-lg border border-slate-200 p-2 text-slate-700 lg:hidden" aria-label="Toggle workspace navigation">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <a href="{{ route('home') }}" class="flex items-center gap-2">
        <img src="{{ asset('images/logo.svg') }}" alt="MWTT" class="h-8 w-auto">
      </a>
      <div class="hidden sm:block">
        <p class="text-sm font-semibold leading-tight text-[#101E33]">Meet with the Techies</p>
        <p class="text-[11px] leading-tight text-slate-500">{{ $super ? 'Super admin workspace' : 'Admin workspace' }}</p>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('home') }}" class="hidden rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50 sm:inline-block">View site</a>
      <a href="{{ $workspaceDashboard }}" class="rounded-lg bg-[#101E33] px-3.5 py-1.5 text-sm font-semibold text-white hover:bg-[#1a2f50]">Workspace home</a>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Logout</button>
      </form>
    </div>
  </div>
</header>

<div class="flex min-h-[calc(100vh-4rem)]">
  <aside class="hidden w-64 shrink-0 border-r border-slate-200 bg-white lg:block">
    <nav class="sticky top-16 max-h-[calc(100vh-4rem)] overflow-y-auto p-4" aria-label="Workspace">
      @foreach($nav as $group => $items)
        @php $groupActive = collect($items)->contains(fn ($item) => $item[2]); @endphp
        <details class="workspace-nav-group" @if($groupActive) open @endif>
          <summary class="mb-1 mt-3 first:mt-0 flex cursor-pointer list-none items-center justify-between rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500 hover:bg-slate-50">
            {{ $group }}
            <svg class="h-3.5 w-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
          </summary>
          <ul class="space-y-0.5">
          @foreach($items as [$label, $url, $active])
            <li>
              <a href="{{ $url }}" @if($active) aria-current="page" @endif
                 class="flex items-center justify-between rounded-lg px-3 py-2 text-sm transition {{ $active ? 'bg-[#FF5C3D]/10 font-semibold text-[#C23A1F]' : 'text-slate-600 hover:bg-slate-50 hover:text-[#101E33]' }}">
                <span>{{ $label }}</span>
                @if($active)<span class="h-1.5 w-1.5 rounded-full bg-[#FF5C3D]"></span>@endif
              </a>
            </li>
          @endforeach
          </ul>
        </details>
      @endforeach
    </nav>
  </aside>

  <div class="min-w-0 flex-1">
    <div id="mobile-workspace-navigation" x-show="mobileNavOpen" x-cloak class="border-b border-slate-200 bg-white lg:hidden">
      <nav class="max-h-[70vh] overflow-y-auto p-4" aria-label="Workspace mobile">
        @foreach($nav as $group => $items)
          @php $groupActive = collect($items)->contains(fn ($item) => $item[2]); @endphp
          <details class="workspace-nav-group" @if($groupActive) open @endif>
            <summary class="mb-1 mt-3 first:mt-0 flex cursor-pointer list-none items-center justify-between rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500 hover:bg-slate-50">
              {{ $group }}
              <svg class="h-3.5 w-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
            </summary>
            <ul class="space-y-0.5">
            @foreach($items as [$label, $url, $active])
              <li><a href="{{ $url }}" @if($active) aria-current="page" @endif class="block rounded-lg px-3 py-2 text-sm {{ $active ? 'bg-[#FF5C3D]/10 font-semibold text-[#C23A1F]' : 'text-slate-600' }}">{{ $label }}</a></li>
            @endforeach
            </ul>
          </details>
        @endforeach
      </nav>
    </div>

    @include('partials.flash')
    @yield('content')
  </div>
</div>
</div>
</body>
</html>
