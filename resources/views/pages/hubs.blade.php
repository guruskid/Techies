@extends('layouts.app')
@section('content')
@include('partials.page-hero', [
  'kicker' => 'Where it happens',
  'title' => 'Technology hubs across the network.',
  'subtitle' => 'Discover verified hubs, innovation centres and community spaces.',
])
<div class="max-w-7xl mx-auto px-4 py-8">
  <form method="GET" class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
    <div class="grid md:grid-cols-12 gap-3 items-end">
      <div class="md:col-span-6">
        <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
        <input name="search" value="{{ $search ?? '' }}" placeholder="Hub name, city, description..." class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
      </div>
      <div class="md:col-span-4">
        <label class="block text-xs font-medium text-slate-500 mb-1">State</label>
        <select name="state" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          <option value="">All states</option>
          @foreach (['Lagos','Abuja','Rivers','Oyo','Enugu','Edo','Akwa Ibom'] as $st)
            <option value="{{ $st }}" @selected(($state ?? '')===$st)>{{ $st }}</option>
          @endforeach
        </select>
      </div>
      <div class="md:col-span-2 flex gap-2">
        <button class="flex-1 px-4 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Search</button>
      </div>
    </div>
    @if(($search ?? '') || ($state ?? ''))
      <div class="mt-3 text-sm text-slate-500">Showing results. <a href="/hubs" class="text-[#FF5C3D] hover:underline">Clear filters</a></div>
    @endif
  </form>

  <div class="grid md:grid-cols-3 gap-4 mt-8">
    @forelse($hubs as $h)
      <a href="/hubs/{{ $h->slug }}" class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition block">
        <div class="flex items-center gap-3 mb-3">
          @if($h->logo)
            <img src="{{ $h->logo }}" class="w-12 h-12 rounded-xl object-cover" alt="{{ $h->name }}">
          @else
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center font-bold">{{ strtoupper(substr($h->name,0,1)) }}</div>
          @endif
          <div>
            <div class="font-semibold text-[#101E33]">{{ $h->name }}</div>
            <div class="text-xs text-slate-500">{{ $h->city }}{{ $h->state ? ', '.$h->state : '' }}</div>
          </div>
          @if($h->is_verified)<span class="ml-auto text-xs px-2 py-1 rounded-full bg-green-50 text-green-600 font-medium">Verified</span>@endif
        </div>
        <p class="text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($h->description,120) }}</p>
      </a>
    @empty
      <p class="text-slate-500 col-span-3">No hubs found.</p>
    @endforelse
  </div>
  <div class="mt-8">{{ $hubs->links() }}</div>
</div>
@endsection
