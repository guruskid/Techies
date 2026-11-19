@extends('layouts.app')
@section('content')
@php
  $search   = $search ?? '';
  $category = (string) ($category ?? '');
  $state    = $state ?? '';
  $type     = $type ?? '';

  $states = ['Lagos','Abuja','Rivers','Oyo','Enugu','Edo','Akwa Ibom'];
  // A state from a shared link that is not in the list still shows as selected
  $stateOptions = $state && ! in_array($state, $states, true) ? array_merge($states, [$state]) : $states;

  $labelClass = 'block text-sm font-medium text-slate-700 mb-1';
  $fieldClass = 'w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';

  $categoryName = $category !== '' ? optional($categories->firstWhere('id', $category))->name : null;
  $typeLabel = ['online' => 'Online', 'physical' => 'In person'][$type] ?? null;

  // Active filters, each with the URL that removes only that filter
  $active = collect([
    'search'   => $search !== '' ? 'Search: '.$search : null,
    'category' => $categoryName,
    'state'    => $state ?: null,
    'type'     => $typeLabel,
  ])->filter();
  $without = fn ($key) => '/events'.(($q = http_build_query(array_filter(
      ['search' => $search, 'category' => $category, 'state' => $state, 'type' => $type],
      fn ($v, $k) => $v !== '' && $k !== $key, ARRAY_FILTER_USE_BOTH))) ? '?'.$q : '');
@endphp

<section class="bg-[#101E33] text-white py-10 md:py-12">
  <div class="max-w-7xl mx-auto px-4">
    <h1 class="text-3xl font-bold">Events</h1>
    <p class="mt-2 text-slate-300">Discover meetups, workshops, conferences and more.</p>
  </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-8">

  <form method="GET" action="/events" role="search" aria-label="Search events" class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-100 shadow-sm space-y-4">
    <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
      <div class="flex-1">
        <label for="search" class="{{ $labelClass }}">Search</label>
        <input id="search" type="search" name="search" value="{{ $search }}" placeholder="Event name, city..." class="{{ $fieldClass }} px-4">
      </div>
      <button type="submit" class="px-6 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition {{ $focus }}">Search</button>
    </div>
    <div class="grid sm:grid-cols-3 gap-3">
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
        <label for="state" class="{{ $labelClass }}">State</label>
        <select id="state" name="state" onchange="this.form.submit()" class="{{ $fieldClass }}">
          <option value="">All states</option>
          @foreach($stateOptions as $st)
            <option value="{{ $st }}" @selected($state === $st)>{{ $st }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label for="type" class="{{ $labelClass }}">Type</label>
        <select id="type" name="type" onchange="this.form.submit()" class="{{ $fieldClass }}">
          <option value="">All</option>
          <option value="online" @selected($type === 'online')>Online</option>
          <option value="physical" @selected($type === 'physical')>In person</option>
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
        <a href="/events" class="text-sm font-medium text-[#FF5C3D] hover:underline {{ $focus }} rounded">Clear all</a>
      @endif
    </div>
  @endif

  <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
    @forelse($events as $e)
      @php
        $start = $e->start_date;
        $end = $e->end_date;
        $time = $e->start_time ? \Carbon\Carbon::parse($e->start_time)->format('g:i A') : null;
        $when = $start->format('M j, Y').($end && ! $end->isSameDay($start) ? ' to '.$end->format('M j, Y') : '').($time ? ', '.$time : '');
        $where = $e->is_online ? 'Online' : ($e->city ?: null);
      @endphp
      <li>
        <a href="/events/{{ $e->slug }}" class="h-full flex flex-col bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-md transition {{ $focus }}">
          @if($e->cover_image)
            <img src="{{ $e->cover_image }}" alt="" loading="lazy" class="h-36 w-full object-cover">
          @else
            <div class="h-36 bg-gradient-to-br from-[#101E33] to-[#FF5C3D] flex items-center justify-center text-white text-4xl font-bold" aria-hidden="true">{{ strtoupper(substr($e->title,0,1)) }}</div>
          @endif
          <div class="p-5 flex flex-col flex-1">
            <div class="text-xs text-[#C23A1F] font-medium">{{ $when }}</div>
            <h2 class="mt-2 font-semibold text-[#101E33]">{{ $e->title }}</h2>
            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-600">
              @if($where)<span>{{ $where }}</span>@endif
              @if($e->category)<span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">{{ $e->category->name }}</span>@endif
              @if(($e->registration_type ?? null) === 'paid')
                <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-800">Paid</span>
              @elseif(($e->registration_type ?? null) === 'free')
                <span class="px-2 py-0.5 rounded-full bg-green-50 text-green-700">Free</span>
              @endif
            </div>
          </div>
        </a>
      </li>
    @empty
      <li class="sm:col-span-2 lg:col-span-3 bg-white rounded-2xl border border-slate-100 p-10 text-center">
        <p class="font-medium text-[#101E33]">No events found</p>
        <p class="mt-1 text-sm text-slate-600">
          @if($active->isNotEmpty())
            Try removing a filter or searching with different words.
            <a href="/events" class="text-[#FF5C3D] hover:underline">Clear all filters</a>
          @else
            Upcoming meetups, workshops and conferences will appear here.
          @endif
        </p>
      </li>
    @endforelse
  </ul>

  <nav class="mt-8" aria-label="Pagination">{{ $events->withQueryString()->links() }}</nav>
</div>
@endsection