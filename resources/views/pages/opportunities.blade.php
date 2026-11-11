@extends('layouts.app')
@section('content')
<section class="bg-[#101E33] text-white py-12">
  <div class="max-w-7xl mx-auto px-4">
    <h1 class="text-3xl font-bold">Opportunities</h1>
    <p class="mt-2 text-slate-300">Jobs, internships, scholarships, grants and more.</p>
  </div>
</section>
<div class="max-w-7xl mx-auto px-4 py-8">
  <form method="GET" class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
    <div class="grid md:grid-cols-12 gap-3 items-end">
      <div class="md:col-span-5">
        <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
        <input name="search" value="{{ $search ?? '' }}" placeholder="Title, organization, location..." class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
      </div>
      <div class="md:col-span-4">
        <label class="block text-xs font-medium text-slate-500 mb-1">Category</label>
        <select name="category" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          <option value="">All categories</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected(($category ?? '')==$c->id)>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-medium text-slate-500 mb-1">Work mode</label>
        <select name="remote" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          <option value="">All</option>
          <option value="1" @selected(($remote ?? '')==='1')>Remote</option>
          <option value="0" @selected(($remote ?? '')==='0')>Onsite</option>
        </select>
      </div>
      <div class="md:col-span-1 flex gap-2">
        <button class="flex-1 px-4 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Go</button>
      </div>
    </div>
    @if(($search ?? '') || ($category ?? '') || ($remote ?? ''))
      <div class="mt-3 text-sm text-slate-500">Showing results. <a href="/opportunities" class="text-[#FF5C3D] hover:underline">Clear filters</a></div>
    @endif
  </form>

  <div class="grid md:grid-cols-3 gap-4 mt-8">
    @forelse($opportunities as $o)
      <a href="/opportunities/{{ $o->slug }}" class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition block">
        <span class="text-xs px-2 py-1 rounded-full bg-[#FF5C3D]/10 text-[#FF5C3D] font-medium">{{ $o->category?->name ?? 'Opportunity' }}</span>
        <h3 class="mt-3 font-semibold text-[#101E33]">{{ $o->title }}</h3>
        <p class="mt-1 text-sm text-slate-500">{{ $o->organization }}</p>
        <div class="mt-3 flex items-center gap-2 text-xs text-slate-400">
          <span>{{ $o->location ?? ($o->is_remote ? 'Remote' : '') }}</span>
          @if($o->is_remote)<span class="px-2 py-0.5 rounded-full bg-green-50 text-green-600">Remote</span>@endif
        </div>
        @if($o->deadline)<div class="mt-2 text-xs text-slate-400">Deadline: {{ $o->deadline }}</div>@endif
      </a>
    @empty
      <p class="text-slate-500 col-span-3">No opportunities found.</p>
    @endforelse
  </div>
  <div class="mt-8">{{ $opportunities->links() }}</div>
</div>
@endsection
