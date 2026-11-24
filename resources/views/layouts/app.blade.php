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
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
<style>
  :root{--brand:#FF5C3D;--ink:#0A1220;--paper:#F7F8FC}
  html{scroll-behavior:smooth}
  body{font-family:Inter,system-ui,sans-serif;background:var(--paper)}
  .font-display{font-family:'Manrope',Inter,sans-serif;letter-spacing:-.02em}
  [x-cloak]{display:none !important}
  .tech-grid{background-image:linear-gradient(rgba(255,255,255,.055) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.055) 1px,transparent 1px);background-size:44px 44px}
  .tech-grid-dark{background-image:linear-gradient(rgba(10,18,32,.045) 1px,transparent 1px),linear-gradient(90deg,rgba(10,18,32,.045) 1px,transparent 1px);background-size:44px 44px}
  .glass{background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.12);backdrop-filter:blur(16px)}
  .text-gradient{background:linear-gradient(92deg,#fff 10%,#ffd8cf 55%,#ffb0a0 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
  .btn-glow{box-shadow:0 18px 45px -16px rgba(255,92,61,.8)}
  .card-lift{transition:transform .28s ease,box-shadow .28s ease,border-color .28s ease}
  .card-lift:hover{transform:translateY(-4px);box-shadow:0 28px 70px -38px rgba(10,18,32,.45);border-color:rgba(255,92,61,.35)}
  .aurora{background:
    radial-gradient(42rem 42rem at 82% 18%,rgba(255,92,61,.18),transparent 62%),
    radial-gradient(34rem 34rem at 12% 82%,rgba(34,211,238,.12),transparent 60%),
    linear-gradient(180deg,#080F1D 0%,#0A1220 60%,#0C1528 100%)}
  .section-label{font-size:.72rem;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#FF5C3D}
  .prose-tech{max-width:70ch;color:#475569;line-height:1.8}
  .prose-tech h2{font-family:'Space Grotesk',Inter,sans-serif;font-size:1.5rem;font-weight:700;color:#101E33;margin:2rem 0 .75rem}
  .prose-tech h3{font-family:'Space Grotesk',Inter,sans-serif;font-weight:700;color:#101E33;margin:1.5rem 0 .5rem}
  .prose-tech p{margin-bottom:1rem}
  .prose-tech ul{list-style:disc;padding-left:1.25rem;margin-bottom:1rem}
  .prose-tech ol{list-style:decimal;padding-left:1.25rem;margin-bottom:1rem}
  .prose-tech blockquote{border-left:3px solid #FF5C3D;padding-left:1rem;color:#334155;font-style:italic;margin:1.25rem 0}
  .prose-tech a{color:#C23A1F;text-decoration:underline}
  .prose-tech img{border-radius:1rem;margin:1.25rem 0}
</style>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stack('head')
</head>
<body class="bg-[#F7F8FC] text-slate-800 antialiased overflow-x-clip">
<noscript><style>#preloader{display:none !important}</style></noscript>
<div id="preloader" class="fixed inset-0 z-[9999] flex items-center justify-center bg-[#101E33] transition-opacity duration-500">
  <div class="flex flex-col items-center gap-4">
    <div class="w-16 h-16 border-4 border-white/20 border-t-[#FF5C3D] rounded-full animate-spin"></div>
    <div class="text-white font-bold text-2xl tracking-widest">MWTT</div>
    <div class="text-slate-400 text-sm">Loading...</div>
  </div>
</div>
<script>
(function () {
  function hidePreloader() {
    var p = document.getElementById('preloader');
    if (p) {
      p.style.opacity = '0';
      setTimeout(function () { p.remove(); }, 500);
    }
  }
  window.addEventListener('load', hidePreloader);
  // Never keep the page covered if a resource stalls.
  setTimeout(hidePreloader, 4000);
})();
</script>
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-xl border-b border-slate-200 shadow-sm">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
    <a href="{{ route('home') }}" class="font-bold text-xl text-[#101E33] flex items-center gap-2 shrink-0">
      <img src="{{ asset('images/logo.svg') }}" alt="MWTT" class="h-8 w-auto">
    </a>
    <nav class="hidden md:flex items-center gap-1 text-sm">
      @php
        $navGroups = [
          ['label' => 'Home', 'url' => '/', 'active' => request()->is('/')],
          [
            'label' => 'Community',
            'active' => request()->is('members', 'members/*', 'hubs*', 'chapters*', 'about*'),
            'items' => [
              ['Members', '/members', request()->is('members', 'members/*')],
              ['Hubs', '/hubs', request()->is('hubs*')],
              ['Chapters', '/chapters', request()->is('chapters*')],
              ['About', '/about', request()->is('about*')],
            ],
          ],
          [
            'label' => 'Events',
            'active' => request()->is('events*', 'speaker-calls*'),
            'items' => [
              ['Events', '/events', request()->is('events*')],
              ['Call for Speakers', '/speaker-calls', request()->is('speaker-calls*')],
            ],
          ],
          ['label' => 'Opportunities', 'url' => '/opportunities', 'active' => request()->is('opportunities*')],
          [
            'label' => 'Learn',
            'active' => request()->is('articles*', 'news*'),
            'items' => [
              ['Articles', '/articles', request()->is('articles*')],
              ['News', '/news', request()->is('news*')],
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
          <li><a href="/chapters" class="text-slate-400 hover:text-white transition">Chapters</a></li>
          <li><a href="/membership" class="text-slate-400 hover:text-white transition">Membership</a></li>
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
