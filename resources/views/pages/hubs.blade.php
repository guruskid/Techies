@extends('layouts.app')
@section('content')
@include('partials.page-hero', [
  'kicker' => 'Where it happens',
  'title' => 'Technology hubs across the network.',
  'subtitle' => 'Discover verified hubs, innovation centres and community spaces.',
  'actions' => [
    ['label' => 'Register your hub', 'url' => route('hubs.register'), 'style' => 'primary'],
  ],
])
<div class="max-w-7xl mx-auto px-4 py-8">
  <form method="GET" class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
    <div class="grid md:grid-cols-12 gap-3 items-end">
      <div class="md:col-span-5">
        <label for="hub-search" class="block text-xs font-medium text-slate-500 mb-1">Search</label>
        <input id="hub-search" name="search" value="{{ $search ?? '' }}" placeholder="Hub name, city, description..." class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
      </div>
      <div class="md:col-span-3">
        <label class="block text-xs font-medium text-slate-500 mb-1">State</label>
        <select name="state" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          <option value="">All states</option>
          @foreach (['Lagos','Abuja','Rivers','Oyo','Enugu','Edo','Akwa Ibom'] as $st)
            <option value="{{ $st }}" @selected(($state ?? '')===$st)>{{ $st }}</option>
          @endforeach
        </select>
      </div>
      <div class="md:col-span-3">
        <label for="organization_type" class="block text-xs font-medium text-slate-500 mb-1">Hub type</label>
        <select id="organization_type" name="organization_type" onchange="this.form.submit()" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          <option value="">All hub types</option>
          @foreach($organizationTypes as $type)
            <option value="{{ $type }}" @selected($organizationType === $type)>{{ $type }}</option>
          @endforeach
        </select>
      </div>
      <div class="md:col-span-1">
        <button class="w-full px-4 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Search</button>
      </div>
    </div>
    @if(($search ?? '') || ($state ?? '') || ($organizationType ?? ''))
      <div class="mt-3 text-sm text-slate-500">Showing filtered hubs. <a href="{{ route('hubs') }}" class="text-[#FF5C3D] hover:underline">Clear filters</a></div>
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
        @if($h->organization_type)
          <span class="mt-4 inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">{{ $h->organization_type }}</span>
        @endif
      </a>
    @empty
      <div class="md:col-span-3 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center">
        <p class="font-semibold text-[#101E33]">No hubs match those filters.</p>
        <p class="mt-2 text-sm text-slate-600">Clear the filters or help grow the directory by registering your hub.</p>
        <div class="mt-5 flex flex-wrap justify-center gap-3">
          <a href="{{ route('hubs') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Clear filters</a>
          <a href="{{ route('hubs.register') }}" class="rounded-lg bg-[#FF5C3D] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">Register your hub</a>
        </div>
      </div>
    @endforelse
  </div>
  <div class="mt-8">{{ $hubs->links() }}</div>
</div>
@endsection
