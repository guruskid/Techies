@extends('layouts.app')
@section('content')
@php
  $search   = $search ?? '';
  $category = (string) ($category ?? '');
  $remote   = (string) ($remote ?? '');   // "0" (onsite) is a real filter, so compare as strings, not truthiness

  $labelClass = 'block text-sm font-medium text-slate-700 mb-1';
  $fieldClass = 'w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';

  $categoryName = $category !== '' ? optional($categories->firstWhere('id', $category))->name : null;
  $modeLabel = ['1' => 'Remote', '0' => 'Onsite'][$remote] ?? null;

  // Active filters, each with the URL that removes only that filter
  $active = collect([
    'search'   => $search !== '' ? 'Search: '.$search : null,
    'category' => $categoryName,
    'remote'   => $modeLabel,
  ])->filter();
  $without = fn ($key) => '/opportunities'.(($q = http_build_query(array_filter(
      ['search' => $search, 'category' => $category, 'remote' => $remote],
      fn ($v, $k) => $v !== '' && $k !== $key, ARRAY_FILTER_USE_BOTH))) ? '?'.$q : '');
@endphp

@include('partials.page-hero', [
  'kicker' => 'Make your next move',
  'title' => 'Opportunities for people building what is next.',
  'subtitle' => 'Jobs, internships, scholarships, grants and fellowships curated for the community.',
])

<div class="max-w-7xl mx-auto px-4 py-8">

  <form method="GET" action="/opportunities" role="search" aria-label="Search opportunities" class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-100 shadow-sm space-y-4">
    <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
      <div class="flex-1">
        <label for="search" class="{{ $labelClass }}">Search</label>
        <input id="search" type="search" name="search" value="{{ $search }}" placeholder="Title, organization, location..." class="{{ $fieldClass }} px-4">
      </div>
      <button type="submit" class="px-6 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition {{ $focus }}">Search</button>
    </div>
    <div class="grid sm:grid-cols-2 gap-3">
      <div>
        <label for="category" class="{{ $labelClass }}">Category</label>
        <select id="category" name="category" onchange="this.form.submit()" class="{{ $fieldClass }}">
          <option value="">All categories</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected($category === (string) $c->id)>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label for="remote" class="{{ $labelClass }}">Work mode</label>
        <select id="remote" name="remote" onchange="this.form.submit()" class="{{ $fieldClass }}">
          <option value="">All</option>
          <option value="1" @selected($remote === '1')>Remote</option>
          <option value="0" @selected($remote === '0')>Onsite</option>
        </select>
      </div>
    </div>
  </form>

  {{-- Active filters --}}
  @if($active->isNotEmpty())
    <div class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-2">
      @foreach($active as $key => $label)
        <a href="{{ $without($key) }}" class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 rounded-full bg-[#FF5C3D]/10 text-[#C23A1F] text-sm hover:bg-[#FF5C3D]/20 {{ $focus }}">
          {{ $label }}
          <span aria-hidden="true">&times;</span>
          <span class="sr-only">Remove filter</span>
        </a>
      @endforeach
      @if($active->count() > 1)
        <a href="/opportunities" class="text-sm font-medium text-[#FF5C3D] hover:underline {{ $focus }} rounded">Clear all</a>
      @endif
    </div>
  @endif

  <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
    @forelse($opportunities as $o)
      @php
        $deadline = $o->deadline;
        $closed = $deadline && $deadline->copy()->endOfDay()->isPast();
        $soon = $deadline && ! $closed && now()->diffInDays($deadline) <= 7;
      @endphp
      <li>
        <a href="/opportunities/{{ $o->slug }}" class="h-full flex flex-col bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition {{ $focus }} {{ $closed ? 'opacity-75' : '' }}">
          <span class="self-start text-xs px-2 py-1 rounded-full bg-[#FF5C3D]/10 text-[#C23A1F] font-medium">{{ $o->category?->name ?? 'Opportunity' }}</span>
          <h2 class="mt-3 font-semibold text-[#101E33]">{{ $o->title }}</h2>
          @if($o->organization)<p class="mt-1 text-sm text-slate-600">{{ $o->organization }}</p>@endif

          <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-600">
            @if($o->location)<span>{{ $o->location }}</span>@endif
            @if($o->is_remote)<span class="px-2 py-0.5 rounded-full bg-green-50 text-green-700">Remote</span>@endif
            @if($o->is_member_only)<span class="px-2 py-0.5 rounded-full bg-[#101E33] text-white">Members only</span>@endif
          </div>

          @if($deadline)
            <div class="mt-auto pt-3 text-xs">
              @if($closed)
                <span class="text-slate-500">Closed on {{ $deadline->format('M j, Y') }}</span>
              @else
                <span class="{{ $soon ? 'text-[#C23A1F] font-medium' : 'text-slate-600' }}">
                  {{ $soon ? 'Closing soon: ' : 'Deadline: ' }}{{ $deadline->format('M j, Y') }}
                </span>
              @endif
            </div>
          @endif
        </a>
      </li>
    @empty
      <li class="sm:col-span-2 lg:col-span-3 bg-white rounded-2xl border border-slate-100 p-10 text-center">
        <p class="font-medium text-[#101E33]">No opportunities found</p>
        <p class="mt-1 text-sm text-slate-600">
          @if($active->isNotEmpty())
            Try removing a filter or searching with different words.
            <a href="/opportunities" class="text-[#FF5C3D] hover:underline">Clear all filters</a>
          @else
            New jobs, internships and grants will be listed here.
          @endif
        </p>
      </li>
    @endforelse
  </ul>

  <nav class="mt-8" aria-label="Pagination">{{ $opportunities->withQueryString()->links() }}</nav>
</div>
@endsection