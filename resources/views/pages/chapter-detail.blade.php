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
      <p class="text-slate-300 mt-1">{{ $c->location }}</p>
      <span class="inline-block mt-2 text-xs px-2 py-1 rounded-full bg-green-500/20 text-green-300">{{ ucfirst($c->status) }}</span>
    </div>
  </div>
</section>
<div class="max-w-7xl mx-auto px-4 py-12 grid md:grid-cols-3 gap-8">
  <div class="md:col-span-2 space-y-6">
    <div class="bg-white rounded-2xl p-8 border border-slate-100 shadow-sm">
      <h2 class="text-2xl font-bold text-[#101E33] mb-3">About this Chapter</h2>
      <p class="text-slate-600 leading-relaxed text-lg">{{ $c->description ?? 'No description provided.' }}</p>
    </div>
    <div class="bg-white rounded-2xl p-8 border border-slate-100 shadow-sm">
      <h2 class="text-2xl font-bold text-[#101E33] mb-3">Recent Events</h2>
      <p class="text-slate-500 text-sm">Events organized by this chapter will appear here.</p>
    </div>
  </div>
  <aside class="space-y-6">
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm sticky top-24">
      <h3 class="text-lg font-bold text-[#101E33] mb-4">Chapter Info</h3>
      <dl class="space-y-4 text-sm">
        <div class="flex justify-between gap-3"><dt class="text-slate-500">Location</dt><dd class="font-medium text-[#101E33] text-right">{{ $c->location ?? '—' }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-slate-500">Coordinator</dt><dd class="font-medium text-[#101E33] text-right">{{ $c->coordinator?->name ?? '—' }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-slate-500">Status</dt><dd class="font-medium text-[#101E33] text-right">{{ ucfirst($c->status) }}</dd></div>
      </dl>
      <div class="mt-6 space-y-2">
        <a href="/chapters" class="block w-full text-center px-6 py-3 border border-slate-200 rounded-lg font-medium hover:bg-slate-50 transition">Back to Chapters</a>
      </div>
    </div>
  </aside>
</div>
@endsection
