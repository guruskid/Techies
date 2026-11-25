<!DOCTYPE html>
<html lang="en">
<head>
@php
  $defaultMetaDescription = \App\Models\Setting::value(
    'default_meta_description',
    'Meet with the Techies is a non-profit technology community connecting professionals, developers, innovators, founders, students and technology hubs.'
  );
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title','Meet with the Techies (MWTT)')</title>
<meta name="description" content="@yield('meta_description', $defaultMetaDescription)">
<meta name="keywords" content="@yield('meta_keywords','tech community, Nigeria, developers, hubs, events, opportunities')">
<link rel="canonical" href="@yield('canonical', url()->current())">
<meta property="og:title" content="@yield('title','Meet with the Techies (MWTT)')">
<meta property="og:description" content="@yield('meta_description', $defaultMetaDescription)">
<meta property="og:type" content="@yield('og_type','website')">
<meta property="og:url" content="{{ url()->current() }}">
@hasSection('og_image')
<meta property="og:image" content="@yield('og_image')">
<meta name="twitter:image" content="@yield('og_image')">
@endif
<meta property="og:site_name" content="Meet with the Techies (MWTT)">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@yield('title','Meet with the Techies (MWTT)')">
<meta name="twitter:description" content="@yield('meta_description', $defaultMetaDescription)">
<link rel="alternate" type="application/rss+xml" title="MWTT Articles" href="{{ route('feed.articles') }}">
<link rel="alternate" type="application/rss+xml" title="MWTT Events" href="{{ route('feed.events') }}">
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
@vite(['resources/css/app.css', 'resources/js/app.js'])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
@include('partials.head-styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stack('head')
</head>
<body class="bg-[#F4F6F8] text-slate-800 antialiased overflow-x-clip">
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-xl border-b border-slate-200 shadow-sm">
  <div class="max-w-7xl mx-auto px-4 py-3.5 flex items-center justify-between gap-5">
    <a href="{{ route('home') }}" class="font-bold text-xl text-[#101E33] flex items-center gap-2 shrink-0">
      <img src="{{ asset('images/logo.svg') }}" alt="MWTT" class="h-8 w-auto">
    </a>
    <nav class="hidden md:flex items-center gap-1 text-sm">
      @php
        $navGroups = [
          ['label' => 'Home', 'url' => '/', 'active' => request()->is('/')],
          ['label' => 'Search', 'url' => route('search'), 'active' => request()->is('search')],
          [
            'label' => 'Community',
            'active' => request()->is('members', 'members/*', 'hubs*', 'chapters*', 'about*', 'projects*', 'grants*'),
            'items' => [
              ['Members', '/members', request()->is('members', 'members/*')],
              ['Hubs', '/hubs', request()->is('hubs*')],
              ['Register a hub', route('hubs.register'), request()->is('hubs/register')],
              ['Chapters', '/chapters', request()->is('chapters*')],
              ['Project showcase', '/projects', request()->is('projects*')],
              ['Micro-grants', '/grants', request()->is('grants*')],
              ['About', '/about', request()->is('about*')],
            ],
          ],
          [
            'label' => 'Events',
            'active' => request()->is('events*', 'speaker-calls*', 'programmes*'),
            'items' => [
              ['Events', '/events', request()->is('events*')],
              ['Call for Speakers', '/speaker-calls', request()->is('speaker-calls*')],
              ['Programmes', '/programmes', request()->is('programmes*')],
            ],
          ],
          ['label' => 'Opportunities', 'url' => '/opportunities', 'active' => request()->is('opportunities*')],
          [
            'label' => 'Learn',
            'active' => request()->is('articles*', 'news*', 'cohorts*', 'benefits*'),
            'items' => [
              ['Articles', '/articles', request()->is('articles*')],
              ['News', '/news', request()->is('news*')],
              ['Cohorts', '/cohorts', request()->is('cohorts*')],
              ['Benefits', '/benefits', request()->is('benefits*')],
            ],
          ],
          ['label' => 'Contact', 'url' => '/contact', 'active' => request()->is('contact*')],
        ];
      @endphp
      @foreach($navGroups as $item)
        @if(isset($item['items']))
          <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
            <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] {{ $item['active'] ? 'text-[#FF5C3D] font-semibold' : 'text-slate-700 hover:text-[#C23A1F] hover:bg-slate-50' }}">
              {{ $item['label'] }}
              <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            <div x-show="open" x-cloak x-transition.origin.top.left
                 class="absolute left-0 mt-2 w-60 rounded-xl border border-white/10 bg-white border-slate-100 shadow-xl p-2 z-50">
              @foreach($item['items'] as [$label, $url, $active])
                <a href="{{ $url }}" @if($active) aria-current="page" @endif
                   class="block px-3 py-2 rounded-lg text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] {{ $active ? 'bg-[#FF5C3D]/10 text-[#C23A1F] font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-[#C23A1F]' }}">{{ $label }}</a>
              @endforeach
            </div>
          </div>
        @else
          <a href="{{ $item['url'] }}" @if($item['active']) aria-current="page" @endif
             class="px-2.5 py-1.5 rounded-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] {{ $item['active'] ? 'text-[#FF5C3D] font-semibold' : 'text-slate-700 hover:text-[#C23A1F] hover:bg-slate-50' }}">{{ $item['label'] }}</a>
        @endif
      @endforeach
    </nav>
    <div class="flex gap-2 items-center">
      <button type="button" x-data @click="$dispatch('toggle-menu')" class="md:hidden p-2 rounded-lg border border-slate-200 text-slate-700" aria-label="Menu">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      @auth
        @php
          $dashboardUrl = auth()->user()->isSuperAdmin()
            ? route('super-admin.dashboard')
            : (auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard'));
        @endphp
        @if(auth()->user()->isSuperAdmin())
          <span class="hidden sm:inline-block px-2.5 py-1 rounded-full text-xs font-semibold bg-[#101E33] text-white">Super Admin</span>
        @elseif(auth()->user()->isAdmin())
          <span class="hidden sm:inline-block px-2.5 py-1 rounded-full text-xs font-semibold bg-[#FF5C3D]/10 text-[#FF5C3D]">Admin</span>
        @endif
        <a href="{{ $dashboardUrl }}" class="hidden sm:inline-block px-3.5 py-1.5 text-sm bg-[#FF5C3D] text-white rounded-lg font-semibold btn-glow hover:opacity-90 transition">Dashboard</a>
        <form method="POST" action="{{ route('logout') }}" class="hidden sm:inline-block">
          @csrf
          <button class="px-3 py-1.5 text-sm border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition">Logout</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="hidden sm:inline-block px-3 py-1.5 text-sm border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition">Login</a>
        <a href="{{ route('register') }}" class="hidden sm:inline-block px-3.5 py-1.5 text-sm bg-[#FF5C3D] text-white rounded-lg font-semibold btn-glow hover:opacity-90 transition">Join</a>
      @endauth
    </div>
  </div>
  <div x-data="{ open: false }" @toggle-menu.window="open = !open" x-show="open" x-cloak class="md:hidden border-t border-slate-200 bg-white">
    <nav class="px-4 py-3 space-y-2 text-sm">
      @foreach($navGroups as $item)
        @if(isset($item['items']))
          <details class="py-1">
            <summary class="flex cursor-pointer list-none items-center justify-between py-2 {{ $item['active'] ? 'text-[#FF5C3D] font-semibold' : 'text-slate-700' }}">
              {{ $item['label'] }}
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </summary>
            <div class="pl-4 pb-2 space-y-1">
              @foreach($item['items'] as [$label, $url, $active])
                <a href="{{ $url }}" @if($active) aria-current="page" @endif
                   class="block py-1.5 {{ $active ? 'text-[#FF5C3D] font-semibold' : 'text-slate-500' }}">{{ $label }}</a>
              @endforeach
            </div>
          </details>
        @else
          <a href="{{ $item['url'] }}" @if($item['active']) aria-current="page" @endif
             class="block py-2 {{ $item['active'] ? 'text-[#FF5C3D] font-semibold' : 'text-slate-700' }}">{{ $item['label'] }}</a>
        @endif
      @endforeach
      @auth
        @php
          $dashboardUrl = auth()->user()->isSuperAdmin()
            ? route('super-admin.dashboard')
            : (auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard'));
        @endphp
        @if(auth()->user()->isSuperAdmin())
          <span class="inline-block mb-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#101E33] text-white">Super Admin</span>
        @elseif(auth()->user()->isAdmin())
          <span class="inline-block mb-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#FF5C3D]/10 text-[#FF5C3D]">Admin</span>
        @endif
        <a href="{{ $dashboardUrl }}" class="block py-2 text-[#FF5C3D] font-medium">Dashboard</a>
        <form method="POST" action="{{ route('logout') }}" class="block">
          @csrf
          <button class="py-2 text-slate-700 hover:text-[#FF5C3D] transition">Logout</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="block py-2">Login</a>
        <a href="{{ route('register') }}" class="block py-2 text-[#FF5C3D] font-medium">Join the Community</a>
      @endauth
    </nav>
  </div>
</header>
<main class="min-h-[60vh]">
@include('partials.flash')
@yield('content')
</main>
<footer class="bg-[#101E33] text-slate-300 mt-16">
  <div class="max-w-7xl mx-auto px-4 py-14">
    <div class="grid lg:grid-cols-[1.5fr_1fr_1fr_1fr] gap-10">
      <div>
        <img src="{{ asset('images/logo-light.svg') }}" alt="Meet with the Techies" class="h-9 w-auto">
        <p class="mt-4 text-sm text-slate-400 max-w-sm leading-relaxed">{{ \App\Models\Setting::value('site_tagline', 'Meet with the Techies is a non-profit technology community connecting professionals, developers, innovators, founders, students and technology hubs.') }}</p>
        <div class="mt-6 flex gap-2">
          @foreach(config('techies.social') as $platform => $url)
            @if($url)
              <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $platform === 'x' ? 'X (Twitter)' : ucfirst($platform) }}" class="w-9 h-9 rounded-lg border border-white/10 bg-white/5 flex items-center justify-center hover:bg-[#FF5C3D] hover:border-[#FF5C3D] transition text-[11px] font-semibold uppercase">{{ $platform === 'x' ? 'x' : substr($platform, 0, 2) }}</a>
            @endif
          @endforeach
        </div>
      </div>

      <div>
        <h3 class="text-xs font-bold uppercase tracking-[0.16em] text-white">Organisation</h3>
        <ul class="mt-4 space-y-2.5 text-sm">
          <li><a href="/about" class="text-slate-400 hover:text-white transition">About us</a></li>
          <li><a href="/mission-and-vision" class="text-slate-400 hover:text-white transition">Mission &amp; vision</a></li>
          <li><a href="/leadership" class="text-slate-400 hover:text-white transition">Leadership</a></li>
          <li><a href="/contact" class="text-slate-400 hover:text-white transition">Contact</a></li>
        </ul>
      </div>

      <div>
        <h3 class="text-xs font-bold uppercase tracking-[0.16em] text-white">Community</h3>
        <ul class="mt-4 space-y-2.5 text-sm">
          <li><a href="/register" class="text-slate-400 hover:text-white transition">Join MWTT</a></li>
          <li><a href="/members" class="text-slate-400 hover:text-white transition">Members directory</a></li>
          <li><a href="/hubs" class="text-slate-400 hover:text-white transition">Technology hubs</a></li>
          <li><a href="{{ route('hubs.register') }}" class="text-slate-400 hover:text-white transition">Register a hub</a></li>
          <li><a href="/chapters" class="text-slate-400 hover:text-white transition">Chapters</a></li>
          <li><a href="/membership" class="text-slate-400 hover:text-white transition">Membership</a></li>
          <li><a href="/benefits" class="text-slate-400 hover:text-white transition">Member benefits</a></li>
          <li><a href="/projects" class="text-slate-400 hover:text-white transition">Project showcase</a></li>
          <li><a href="/grants" class="text-slate-400 hover:text-white transition">Micro-grants</a></li>
          <li><a href="/cohorts" class="text-slate-400 hover:text-white transition">Cohorts</a></li>
          <li><a href="/programmes" class="text-slate-400 hover:text-white transition">Programmes</a></li>
        </ul>
      </div>

      <div>
        <h3 class="text-xs font-bold uppercase tracking-[0.16em] text-white">Explore</h3>
        <ul class="mt-4 space-y-2.5 text-sm">
          <li><a href="/events" class="text-slate-400 hover:text-white transition">Events</a></li>
          <li><a href="/opportunities" class="text-slate-400 hover:text-white transition">Opportunities</a></li>
          <li><a href="/speaker-calls" class="text-slate-400 hover:text-white transition">Call for speakers</a></li>
          <li><a href="/articles" class="text-slate-400 hover:text-white transition">Articles</a></li>
          <li><a href="/news" class="text-slate-400 hover:text-white transition">News</a></li>
        </ul>
      </div>
    </div>

    <div class="mt-10 border-t border-white/10 pt-6 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500">
      <p>&copy; {{ date('Y') }} Meet with the Techies (MWTT). All rights reserved.</p>
      <div class="flex flex-wrap gap-5">
        <a href="/privacy-policy" class="hover:text-white transition">Privacy policy</a>
        <a href="/terms" class="hover:text-white transition">Terms of use</a>
        <a href="/community-guidelines" class="hover:text-white transition">Community guidelines</a>
      </div>
    </div>
  </div>
</footer>
</body>
</html>
