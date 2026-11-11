<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title','Meet with the Techies (MWTT)')</title>
<meta name="description" content="@yield('meta_description','Meet with the Techies is a non-profit technology community connecting professionals, developers, innovators, founders, students and technology hubs.')">
<meta name="keywords" content="@yield('meta_keywords','tech community, Nigeria, developers, hubs, events, opportunities')">
<link rel="canonical" href="{{ url()->current() }}">
<meta property="og:title" content="@yield('title','Meet with the Techies (MWTT)')">
<meta property="og:description" content="@yield('meta_description','Meet with the Techies is a non-profit technology community connecting professionals, developers, innovators, founders, students and technology hubs.')">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:site_name" content="Meet with the Techies (MWTT)">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@yield('title','Meet with the Techies (MWTT)')">
<meta name="twitter:description" content="@yield('meta_description','Meet with the Techies is a non-profit technology community connecting professionals, developers, innovators, founders, students and technology hubs.')">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>body{font-family:Inter,sans-serif}</style>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800">
<div id="preloader" class="fixed inset-0 z-[9999] flex items-center justify-center bg-[#101E33] transition-opacity duration-500">
  <div class="flex flex-col items-center gap-4">
    <div class="w-16 h-16 border-4 border-white/20 border-t-[#FF5C3D] rounded-full animate-spin"></div>
    <div class="text-white font-bold text-2xl tracking-widest">MWTT</div>
    <div class="text-slate-400 text-sm">Loading...</div>
  </div>
</div>
<script>
window.addEventListener('load', function () {
  var p = document.getElementById('preloader');
  if (p) { p.style.opacity = '0'; setTimeout(function () { p.remove(); }, 500); }
});
</script>
<header class="sticky top-0 z-40 bg-white border-b border-slate-200">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
    <a href="{{ route('home') }}" class="font-bold text-xl text-slate-900">MWTT</a>
    <nav class="hidden md:flex gap-6 text-sm">
      @php
        $nav = [
          ['Home','/',request()->is('/')],
          ['About','/about',request()->is('about*')],
          ['Members','/members',request()->is('members*')],
          ['Hubs','/hubs',request()->is('hubs*')],
          ['Events','/events',request()->is('events*')],
          ['Opportunities','/opportunities',request()->is('opportunities*')],
          ['Chapters','/chapters',request()->is('chapters*')],
          ['Contact','/contact',request()->is('contact*')],
        ];
      @endphp
      @foreach($nav as [$label,$url,$active])
        <a href="{{ $url }}" class="{{ $active ? 'text-[#FF5C3D] font-semibold border-b-2 border-[#FF5C3D] pb-1' : 'text-slate-700 hover:text-[#FF5C3D] transition' }}">{{ $label }}</a>
      @endforeach
    </nav>
    <div class="flex gap-2 items-center">
      <button x-data @click="$dispatch('toggle-menu')" class="md:hidden p-2 rounded-lg border border-slate-200" aria-label="Menu">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      @auth
        <a href="{{ route('dashboard') }}" class="hidden sm:inline-block px-3 py-1.5 text-sm bg-[#FF5C3D] text-white rounded-lg">Dashboard</a>
      @else
        <a href="{{ route('login') }}" class="hidden sm:inline-block px-3 py-1.5 text-sm border rounded-lg">Login</a>
        <a href="{{ route('register') }}" class="hidden sm:inline-block px-3 py-1.5 text-sm bg-[#FF5C3D] text-white rounded-lg">Join</a>
      @endauth
    </div>
  </div>
  <div x-data="{ open: false }" @toggle-menu.window="open = !open" x-show="open" x-cloak class="md:hidden border-t border-slate-200 bg-white">
    <nav class="px-4 py-3 space-y-2 text-sm">
      <a href="/" class="block py-2">Home</a>
      <a href="/about" class="block py-2">About</a>
      <a href="/members" class="block py-2">Members</a>
      <a href="/hubs" class="block py-2">Hubs</a>
      <a href="/events" class="block py-2">Events</a>
      <a href="/opportunities" class="block py-2">Opportunities</a>
      <a href="/chapters" class="block py-2">Chapters</a>
      <a href="/contact" class="block py-2">Contact</a>
      @auth
        <a href="{{ route('dashboard') }}" class="block py-2 text-[#FF5C3D] font-medium">Dashboard</a>
      @else
        <a href="{{ route('login') }}" class="block py-2">Login</a>
        <a href="{{ route('register') }}" class="block py-2 text-[#FF5C3D] font-medium">Join the Community</a>
      @endauth
    </nav>
  </div>
</header>
<main>@yield('content')</main>
<footer class="bg-[#101E33] text-slate-300 mt-16">
  <div class="max-w-7xl mx-auto px-4 py-14 grid md:grid-cols-5 gap-8">
    <div class="md:col-span-2">
      <div class="text-2xl font-bold text-white">MWTT</div>
      <p class="mt-3 text-sm text-slate-400 max-w-sm">Meet with the Techies is a non-profit technology community connecting professionals, developers, innovators, founders, students and technology hubs.</p>
      <div class="mt-5 flex gap-3">
        <a href="#" class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center hover:bg-[#FF5C3D] transition text-xs">in</a>
        <a href="#" class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center hover:bg-[#FF5C3D] transition text-xs">x</a>
        <a href="#" class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center hover:bg-[#FF5C3D] transition text-xs">ig</a>
        <a href="#" class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center hover:bg-[#FF5C3D] transition text-xs">fb</a>
      </div>
    </div>
    <div>
      <h4 class="text-white font-semibold mb-3">Organization</h4>
      <ul class="space-y-2 text-sm">
        <li><a href="/about" class="hover:text-white transition">About Us</a></li>
        <li><a href="/mission-and-vision" class="hover:text-white transition">Mission & Vision</a></li>
        <li><a href="/leadership" class="hover:text-white transition">Leadership</a></li>
        <li><a href="/contact" class="hover:text-white transition">Contact</a></li>
      </ul>
    </div>
    <div>
      <h4 class="text-white font-semibold mb-3">Community</h4>
      <ul class="space-y-2 text-sm">
        <li><a href="/register" class="hover:text-white transition">Join MWTT</a></li>
        <li><a href="/members" class="hover:text-white transition">Find Techies</a></li>
        <li><a href="/hubs" class="hover:text-white transition">Find Hubs</a></li>
        <li><a href="/chapters" class="hover:text-white transition">Chapters</a></li>
      </ul>
    </div>
    <div>
      <h4 class="text-white font-semibold mb-3">Explore</h4>
      <ul class="space-y-2 text-sm">
        <li><a href="/events" class="hover:text-white transition">Events</a></li>
        <li><a href="/opportunities" class="hover:text-white transition">Opportunities</a></li>
        <li><a href="/community" class="hover:text-white transition">Community</a></li>
        <li><a href="/community-guidelines" class="hover:text-white transition">Guidelines</a></li>
      </ul>
    </div>
  </div>
  <div class="border-t border-white/10">
    <div class="max-w-7xl mx-auto px-4 py-6 flex flex-col md:flex-row items-center justify-between gap-4 text-sm">
      <div class="text-slate-400">© {{ date('Y') }} Meet with the Techies (MWTT). All rights reserved.</div>
      <div class="flex gap-4">
        <a href="/privacy-policy" class="hover:text-white transition">Privacy Policy</a>
        <a href="/terms" class="hover:text-white transition">Terms of Use</a>
        <a href="/community-guidelines" class="hover:text-white transition">Community Guidelines</a>
      </div>
    </div>
  </div>
</footer>
</body>
</html>
