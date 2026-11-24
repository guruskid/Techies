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
<body class="bg-[#F8FAFC] text-slate-800 antialiased">
@php
  $user = auth()->user();
  $super = $user?->isSuperAdmin();
  $nav = [
    'Main' => [
      ['Dashboard', $super ? route('super-admin.dashboard') : route('admin.dashboard'), $super ? request()->is('super-admin') : request()->is('admin', 'admin/dashboard')],
      ['Manage centre', route('admin.manage'), request()->is('admin/manage')],
    ],
    'People' => [
      ['Members', route('admin.members.index'), request()->is('admin/members*')],
      ['Hubs', route('admin.hubs.index'), request()->is('admin/hubs*')],
      ['Chapters', route('admin.chapters.index'), request()->is('admin/chapters*')],
      ['Mentorship', route('admin.mentorship.index'), request()->is('admin/mentorship*')],
      ['Cohorts', route('admin.cohorts.index'), request()->is('admin/cohorts*')],
      ['Showcase', route('admin.showcase.index'), request()->is('admin/showcase*')],
      ['Referrals', route('admin.referrals.index'), request()->is('admin/referrals*')],
      ['Lounge', route('admin.lounge.index'), request()->is('admin/lounge*')],
      ['Grants', route('admin.grants.index'), request()->is('admin/grants*')],
      ['Spotlights', route('admin.spotlights.index'), request()->is('admin/spotlights*')],
      ['Introductions', route('admin.introductions.index'), request()->is('admin/introductions*')],
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

<header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur">
  <div class="flex h-16 items-center justify-between gap-4 px-4 lg:px-6">
    <div class="flex items-center gap-3">
      <button type="button" x-data @click="$dispatch('toggle-admin-nav')" class="rounded-lg border border-slate-200 p-2 text-slate-700 lg:hidden" aria-label="Toggle navigation">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <a href="{{ route('home') }}" class="flex items-center gap-2">
        <img src="{{ asset('images/logo.svg') }}" alt="MWTT" class="h-8 w-auto">
      </a>
      <span class="hidden rounded-full bg-[#101E33] px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white sm:inline-block">{{ $super ? 'Super admin' : 'Admin' }}</span>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('home') }}" class="hidden rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50 sm:inline-block">View site</a>
      <a href="{{ route('dashboard') }}" class="rounded-lg bg-[#FF5C3D] px-3.5 py-1.5 text-sm font-semibold text-white hover:bg-[#e94f32]">Dashboard</a>
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
        <p class="mb-2 mt-4 first:mt-0 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ $group }}</p>
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
      @endforeach
    </nav>
  </aside>

  <div class="min-w-0 flex-1">
    <div x-data="{ open: false }" @toggle-admin-nav.window="open = !open" x-show="open" x-cloak class="border-b border-slate-200 bg-white lg:hidden">
      <nav class="max-h-[70vh] overflow-y-auto p-4" aria-label="Workspace mobile">
        @foreach($nav as $group => $items)
          <p class="mb-1 mt-3 first:mt-0 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ $group }}</p>
          <ul class="space-y-0.5">
            @foreach($items as [$label, $url, $active])
              <li><a href="{{ $url }}" class="block rounded-lg px-3 py-2 text-sm {{ $active ? 'bg-[#FF5C3D]/10 font-semibold text-[#C23A1F]' : 'text-slate-600' }}">{{ $label }}</a></li>
            @endforeach
          </ul>
        @endforeach
      </nav>
    </div>

    @include('partials.flash')
    @yield('content')
  </div>
</div>
</body>
</html>
