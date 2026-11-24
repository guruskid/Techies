@extends('layouts.app')
@section('content')
@php
  $search     = $search ?? '';
  $technology = $technology ?? '';
  $state      = $state ?? '';
  $experience = $experience ?? '';

  $technologies = ['Software Development','Frontend Development','Backend Development','Full-Stack Development','Mobile Development','DevOps and Cloud Engineering','Cybersecurity','Data Science','Artificial Intelligence and Machine Learning','UI/UX Design','Product Management'];
  $states       = ['Lagos','Abuja','Rivers','Oyo','Enugu','Edo','Akwa Ibom'];
  $levels       = ['Student','Beginner','Intermediate','Experienced','Expert'];

  // A filter value that is not in the list (for example from a shared link) still shows as selected
  $withSelected = fn (array $options, $selected) => $selected && ! in_array($selected, $options, true) ? array_merge($options, [$selected]) : $options;

  // Active filters, each with the URL that removes only that filter
  $active = collect([
    'search'     => $search     ? 'Search: '.$search : null,
    'technology' => $technology ?: null,
    'state'      => $state      ?: null,
    'experience' => $experience ?: null,
  ])->filter();
  $without = fn ($key) => '/members?'.http_build_query(array_filter(
      ['search' => $search, 'technology' => $technology, 'state' => $state, 'experience' => $experience],
      fn ($v, $k) => $v && $k !== $key, ARRAY_FILTER_USE_BOTH));

  $membershipFee = (int) \App\Models\Setting::value('membership_fee', '15000');
  $membershipCurrency = (string) \App\Models\Setting::value('membership_currency', 'NGN');
  $membershipPrice = $membershipCurrency === 'NGN' ? '₦'.number_format($membershipFee) : $membershipCurrency.' '.number_format($membershipFee);

  $labelClass = 'block text-sm font-medium text-slate-700 mb-1';
  $fieldClass = 'w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
@endphp

<section class="relative overflow-hidden border-b border-slate-200 bg-white">
  <div class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-slate-50 to-transparent" aria-hidden="true"></div>
  <div class="relative max-w-7xl mx-auto px-4 py-12 md:py-16 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
    <div>
      <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#C23A1F]">The network</p>
      <h1 class="mt-2 font-display text-3xl md:text-5xl font-extrabold text-[#101E33]">Members Directory</h1>
      <p class="mt-3 max-w-2xl text-lg text-slate-600">Find and connect with the builders, designers, founders and students shaping tech across Nigeria.</p>
    </div>
    <a href="{{ route('membership.show') }}" class="group flex shrink-0 items-center gap-4 rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm transition hover:border-[#FF5C3D]/40 {{ $focus }}">
      <div>
        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#C23A1F]">Membership</p>
        <p class="mt-1 font-display text-lg font-bold text-[#101E33]">{{ $membershipPrice }} <span class="text-sm font-normal text-slate-500">one time</span></p>
      </div>
      <span class="shrink-0 text-sm font-semibold text-[#C23A1F] group-hover:underline">Join</span>
    </a>
  </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-8">

  {{-- Filters --}}
  <form method="GET" action="/members" role="search" aria-label="Search members" class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-100 shadow-sm space-y-4">
    <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
      <div class="flex-1">
        <label for="search" class="{{ $labelClass }}">Search</label>
        <input id="search" type="search" name="search" value="{{ $search }}" placeholder="Name, title, skill, city..." class="{{ $fieldClass }} px-4">
      </div>
      <button type="submit" class="px-6 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition {{ $focus }}">Search</button>
    </div>

    <div class="grid sm:grid-cols-3 gap-3">
      <div>
        <label for="technology" class="{{ $labelClass }}">Technology</label>
        <select id="technology" name="technology" onchange="this.form.submit()" class="{{ $fieldClass }}">
          <option value="">All technologies</option>
          @foreach ($withSelected($technologies, $technology) as $t)
            <option value="{{ $t }}" @selected($technology === $t)>{{ $t }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label for="state" class="{{ $labelClass }}">State</label>
        <select id="state" name="state" onchange="this.form.submit()" class="{{ $fieldClass }}">
          <option value="">All states</option>
          @foreach ($withSelected($states, $state) as $st)
            <option value="{{ $st }}" @selected($state === $st)>{{ $st }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label for="experience" class="{{ $labelClass }}">Experience</label>
        <select id="experience" name="experience" onchange="this.form.submit()" class="{{ $fieldClass }}">
          <option value="">All levels</option>
          @foreach ($withSelected($levels, $experience) as $e)
            <option value="{{ $e }}" @selected($experience === $e)>{{ $e }}</option>
          @endforeach
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
        <a href="/members" class="text-sm font-medium text-[#FF5C3D] hover:underline {{ $focus }} rounded">Clear all</a>
      @endif
    </div>
  @endif

  {{-- Results --}}
  <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
    @forelse($members as $m)
      @php
        $mp = $m->profile;
        $loc = implode(', ', array_filter([$mp?->city, $mp?->state]));
        $url = $mp?->username ? $mp->publicUrl() : null;
      @endphp
      <li>
        @if($url)<a href="{{ $url }}" class="h-full bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition flex gap-4 items-center {{ $focus }}">
        @else<div class="h-full bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex gap-4 items-center">@endif
          @if($mp?->avatar)
            <img src="{{ $mp->avatar }}" class="w-12 h-12 shrink-0 rounded-full object-cover" alt="" loading="lazy">
          @else
            <div class="w-12 h-12 shrink-0 rounded-full bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center font-bold" aria-hidden="true">{{ strtoupper(substr($m->name,0,1)) }}</div>
          @endif
          <div class="min-w-0">
            <div class="font-semibold text-[#101E33] truncate">{{ $m->name }}</div>
            <div class="text-sm text-slate-600 truncate">{{ $mp?->professional_title ?? 'Member' }}</div>
            @if($mp?->technology_category)<div class="text-xs text-slate-500 truncate">{{ $mp->technology_category }}</div>@endif
            @if($loc)<div class="text-xs text-slate-500 truncate">{{ $loc }}</div>@endif
          </div>
        @if($url)</a>@else</div>@endif
      </li>
    @empty
      <li class="sm:col-span-2 lg:col-span-3 bg-white rounded-2xl border border-slate-100 p-10 text-center">
        <p class="font-medium text-[#101E33]">No members found</p>
        <p class="mt-1 text-sm text-slate-600">
          @if($active->isNotEmpty())
            Try removing a filter or searching with different words.
            <a href="/members" class="text-[#FF5C3D] hover:underline">Clear all filters</a>
          @else
            Public member profiles will appear here.
          @endif
        </p>
      </li>
    @endforelse
  </ul>

  <nav class="mt-8" aria-label="Pagination">{{ $members->withQueryString()->links() }}</nav>
</div>
@endsection