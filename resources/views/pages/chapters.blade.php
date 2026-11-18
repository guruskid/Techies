@extends('layouts.app')
@section('content')
@php
  $search = $search ?? '';
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
@endphp

<section class="bg-[#101E33] text-white py-10 md:py-12">
  <div class="max-w-7xl mx-auto px-4">
    <h1 class="text-3xl font-bold">Community Chapters</h1>
    <p class="mt-2 text-slate-300">Regional and state chapters across Nigeria.</p>
  </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-8">

  <form method="GET" action="/chapters" role="search" aria-label="Search chapters" class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-100 shadow-sm">
    <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
      <div class="flex-1">
        <label for="search" class="block text-sm font-medium text-slate-700 mb-1">Search</label>
        <input id="search" type="search" name="search" value="{{ $search }}" placeholder="Chapter name, location..."
               class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
      </div>
      <button type="submit" class="px-6 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition {{ $focus }}">Search</button>
    </div>
  </form>

  @if($search)
    <div class="mt-6">
      <a href="/chapters" class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 rounded-full bg-[#FF5C3D]/10 text-[#C23A1F] text-sm hover:bg-[#FF5C3D]/20 {{ $focus }}">
        Search: {{ $search }}
        <span aria-hidden="true">&times;</span>
        <span class="sr-only">Clear search</span>
      </a>
    </div>
  @endif

  <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
    @forelse($chapters as $c)
      <li>
        <a href="/chapters/{{ $c->slug }}" class="h-full flex flex-col bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition {{ $focus }}">
          <div class="flex items-center gap-3 mb-3">
            @if($c->logo)
              <img src="{{ $c->logo }}" class="w-12 h-12 shrink-0 rounded-xl object-cover" alt="" loading="lazy">
            @else
              <div class="w-12 h-12 shrink-0 rounded-xl bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center font-bold" aria-hidden="true">{{ strtoupper(substr($c->name,0,1)) }}</div>
            @endif
            <div class="min-w-0">
              <div class="font-semibold text-[#101E33]">{{ $c->name }}</div>
              @if($c->location)<div class="text-xs text-slate-500">{{ $c->location }}</div>@endif
            </div>
            {{-- Only flag chapters that are not active; "Active" on every card is noise --}}
            @if($c->status && $c->status !== 'active')
              <span class="ml-auto shrink-0 text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-700 font-medium">{{ ucfirst($c->status) }}</span>
            @endif
          </div>
          @if($c->description)
            <p class="text-sm text-slate-600 flex-1">{{ \Illuminate\Support\Str::limit($c->description,120) }}</p>
          @endif
          <span class="mt-4 text-sm font-medium text-[#FF5C3D]">View chapter</span>
        </a>
      </li>
    @empty
      <li class="sm:col-span-2 lg:col-span-3 bg-white rounded-2xl border border-slate-100 p-10 text-center">
        <p class="font-medium text-[#101E33]">No chapters found</p>
        <p class="mt-1 text-sm text-slate-600">
          @if($search)
            Try a different name or location. <a href="/chapters" class="text-[#FF5C3D] hover:underline">Clear search</a>
          @else
            Chapters will be listed here as they open.
          @endif
        </p>
      </li>
    @endforelse
  </ul>
</div>
@endsection