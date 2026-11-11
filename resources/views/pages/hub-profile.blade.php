@extends('layouts.app')
@section('content')
@php $h = $hub; @endphp
<section class="bg-[#101E33] text-white py-16">
  <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center gap-6">
    @if($h->logo)
      <img src="{{ $h->logo }}" class="w-24 h-24 rounded-2xl object-cover border-4 border-white/20" alt="{{ $h->name }}">
    @else
      <div class="w-24 h-24 rounded-2xl bg-white/10 border-4 border-white/20 flex items-center justify-center text-3xl font-bold">{{ strtoupper(substr($h->name,0,1)) }}</div>
    @endif
    <div>
      <h1 class="text-3xl font-bold">{{ $h->name }}</h1>
      <p class="text-slate-300 mt-1">{{ $h->city }}{{ $h->state ? ', '.$h->state : '' }}{{ $h->country ? ', '.$h->country : '' }}</p>
      @if($h->is_verified)<span class="inline-block mt-2 text-xs px-2 py-1 rounded-full bg-green-500/20 text-green-300">Verified Hub</span>@endif
      @if($h->website)<a href="{{ $h->website }}" target="_blank" class="ml-3 text-sm text-[#FF5C3D] hover:underline">Website →</a>@endif
    </div>
  </div>
</section>
<div class="max-w-7xl mx-auto px-4 py-12 grid md:grid-cols-3 gap-8">
  <div class="md:col-span-2 space-y-6">
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33] mb-2">About</h2>
      <p class="text-slate-600 leading-relaxed">{{ $h->description ?? 'No description provided.' }}</p>
    </div>
    @if($h->programs_offered)
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33] mb-2">Programs Offered</h2>
      <p class="text-slate-600 leading-relaxed">{{ $h->programs_offered }}</p>
    </div>
    @endif
  </div>
  <aside class="space-y-6">
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33] mb-3">Details</h2>
      <dl class="space-y-3 text-sm">
        <div><dt class="text-slate-500">Email</dt><dd class="font-medium text-[#101E33]">{{ $h->email ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">Phone</dt><dd class="font-medium text-[#101E33]">{{ $h->phone ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">Community Size</dt><dd class="font-medium text-[#101E33]">{{ $h->community_size ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">Year Established</dt><dd class="font-medium text-[#101E33]">{{ $h->year_established ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">Organization Type</dt><dd class="font-medium text-[#101E33]">{{ $h->organization_type ?? '—' }}</dd></div>
      </dl>
    </div>
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33] mb-3">Social</h2>
      <ul class="space-y-2 text-sm">
        @if($h->linkedin)<li><a href="{{ $h->linkedin }}" class="text-[#FF5C3D] hover:underline" target="_blank">LinkedIn</a></li>@endif
        @if($h->facebook)<li><a href="{{ $h->facebook }}" class="text-[#FF5C3D] hover:underline" target="_blank">Facebook</a></li>@endif
        @if($h->instagram)<li><a href="{{ $h->instagram }}" class="text-[#FF5C3D] hover:underline" target="_blank">Instagram</a></li>@endif
        @if($h->x)<li><a href="{{ $h->x }}" class="text-[#FF5C3D] hover:underline" target="_blank">X</a></li>@endif
        @if($h->youtube)<li><a href="{{ $h->youtube }}" class="text-[#FF5C3D] hover:underline" target="_blank">YouTube</a></li>@endif
        @if(!$h->linkedin && !$h->facebook && !$h->instagram && !$h->x && !$h->youtube)<li class="text-slate-400">No social links.</li>@endif
      </ul>
    </div>
  </aside>
</div>
@endsection
