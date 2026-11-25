@extends('layouts.app')
@section('title', 'Saved items · Meet with the Techies')
@section('content')
@include('partials.member-nav')
<main class="mx-auto max-w-5xl px-4 py-8">
  <div class="mb-6">
    <p class="text-xs font-bold uppercase tracking-widest text-[#C23A1F]">Your shortlist</p>
    <h1 class="mt-2 font-display text-3xl font-extrabold text-[#101E33]">Saved items</h1>
    <p class="mt-2 text-sm text-slate-600">Keep useful opportunities and events together while you decide what to pursue.</p>
  </div>
  <div class="space-y-3">
    @forelse($bookmarks as $bookmark)
      @php
        $item = $bookmark->bookmarkable;
        $title = $item?->title;
        $url = $item instanceof \App\Models\Event
          ? route('events.show', $item->slug)
          : ($item instanceof \App\Models\Opportunity ? route('opportunities.show', $item->slug) : null);
      @endphp
      @if($item && $url)
        <article class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ class_basename($item) === 'Event' ? 'Event' : 'Opportunity' }}</p>
            <a href="{{ $url }}" class="mt-1 block font-semibold text-[#101E33] hover:text-[#C23A1F]">{{ $title }}</a>
          </div>
          <form method="POST" action="{{ route('bookmarks.destroy', $bookmark->id) }}">
            @csrf
            @method('DELETE')
            <button class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Remove</button>
          </form>
        </article>
      @else
        <article class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5">
          <p class="text-sm text-slate-600">This saved item is no longer available.</p>
          <form method="POST" action="{{ route('bookmarks.destroy', $bookmark->id) }}">
            @csrf
            @method('DELETE')
            <button class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Remove</button>
          </form>
        </article>
      @endif
    @empty
      <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
        <h2 class="font-semibold text-[#101E33]">Nothing saved yet</h2>
        <p class="mt-2 text-sm text-slate-600">Save events and opportunities from their listings and they’ll be waiting here.</p>
        <div class="mt-5 flex justify-center gap-3">
          <a href="{{ route('events') }}" class="rounded-lg bg-[#FF5C3D] px-4 py-2 text-sm font-semibold text-white">Browse events</a>
          <a href="{{ route('opportunities') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">Find opportunities</a>
        </div>
      </div>
    @endforelse
  </div>
  <div class="mt-6">{{ $bookmarks->links() }}</div>
</main>
@endsection
