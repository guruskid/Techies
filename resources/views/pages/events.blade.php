@extends('layouts.app')
@section('content')
<section class="bg-[#101E33] text-white py-12">
  <div class="max-w-7xl mx-auto px-4">
    <h1 class="text-3xl font-bold">Events</h1>
    <p class="mt-2 text-slate-300">Discover meetups, workshops, conferences and more.</p>
  </div>
</section>
<div class="max-w-7xl mx-auto px-4 py-8">
  <form method="GET" class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
    <div class="grid md:grid-cols-12 gap-3 items-end">
      <div class="md:col-span-4">
        <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
        <input name="search" value="{{ $search ?? '' }}" placeholder="Event name, city..." class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
      </div>
      <div class="md:col-span-3">
        <label class="block text-xs font-medium text-slate-500 mb-1">Category</label>
        <select name="category" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          <option value="">All categories</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected(($category ?? '')==$c->id)>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-medium text-slate-500 mb-1">State</label>
        <select name="state" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          <option value="">All states</option>
          @foreach (['Lagos','Abuja','Rivers','Oyo','Enugu','Edo','Akwa Ibom'] as $st)
            <option value="{{ $st }}" @selected(($state ?? '')===$st)>{{ $st }}</option>
          @endforeach
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-medium text-slate-500 mb-1">Type</label>
        <select name="type" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          <option value="">All</option>
          <option value="online" @selected(($type ?? '')==='online')>Online</option>
          <option value="physical" @selected(($type ?? '')==='physical')>In person</option>
        </select>
      </div>
      <div class="md:col-span-1 flex gap-2">
        <button class="flex-1 px-4 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Go</button>
      </div>
    </div>
    @if(($search ?? '') || ($category ?? '') || ($state ?? '') || ($type ?? ''))
      <div class="mt-3 text-sm text-slate-500">Showing results. <a href="/events" class="text-[#FF5C3D] hover:underline">Clear filters</a></div>
    @endif
  </form>

  <div class="grid md:grid-cols-3 gap-4 mt-8">
    @forelse($events as $e)
      <a href="/events/{{ $e->slug }}" class="bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-md transition block">
        <div class="h-36 bg-gradient-to-br from-[#101E33] to-[#FF5C3D] flex items-center justify-center text-white text-4xl font-bold">{{ strtoupper(substr($e->title,0,1)) }}</div>
        <div class="p-5">
          <div class="text-xs text-[#FF5C3D] font-medium">{{ $e->start_date }} @if($e->is_online)· Online @else· {{ $e->city }} @endif</div>
          <h3 class="mt-2 font-semibold text-[#101E33]">{{ $e->title }}</h3>
          @if($e->category)<div class="mt-1 text-xs text-slate-400">{{ $e->category->name }}</div>@endif
        </div>
      </a>
    @empty
      <p class="text-slate-500 col-span-3">No events found.</p>
    @endforelse
  </div>
  <div class="mt-8">{{ $events->links() }}</div>
</div>
@endsection
