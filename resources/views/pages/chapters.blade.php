@extends('layouts.app')
@section('content')
<section class="bg-[#101E33] text-white py-12">
  <div class="max-w-7xl mx-auto px-4">
    <h1 class="text-3xl font-bold">Community Chapters</h1>
    <p class="mt-2 text-slate-300">Regional and state chapters across Nigeria.</p>
  </div>
</section>
<div class="max-w-7xl mx-auto px-4 py-8">
  <form method="GET" class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
    <div class="grid md:grid-cols-12 gap-3 items-end">
      <div class="md:col-span-9">
        <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
        <input name="search" value="{{ $search ?? '' }}" placeholder="Chapter name, location..." class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
      </div>
      <div class="md:col-span-3 flex gap-2">
        <button class="flex-1 px-4 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Search</button>
      </div>
    </div>
    @if(($search ?? ''))
      <div class="mt-3 text-sm text-slate-500">Showing results. <a href="/chapters" class="text-[#FF5C3D] hover:underline">Clear filters</a></div>
    @endif
  </form>

  <div class="grid md:grid-cols-3 gap-4 mt-8">
    @forelse($chapters as $c)
      <a href="/chapters/{{ $c->slug }}" class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition block">
        <div class="flex items-center gap-3 mb-3">
          @if($c->logo)
            <img src="{{ $c->logo }}" class="w-12 h-12 rounded-xl object-cover" alt="{{ $c->name }}">
          @else
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center font-bold">{{ strtoupper(substr($c->name,0,1)) }}</div>
          @endif
          <div>
            <div class="font-semibold text-[#101E33]">{{ $c->name }}</div>
            <div class="text-xs text-slate-500">{{ $c->location }}</div>
          </div>
          <span class="ml-auto text-xs px-2 py-1 rounded-full bg-green-50 text-green-600 font-medium">{{ ucfirst($c->status) }}</span>
        </div>
        <p class="text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($c->description,120) }}</p>
      </a>
    @empty
      <p class="text-slate-500 col-span-3">No chapters found.</p>
    @endforelse
  </div>
</div>
@endsection
