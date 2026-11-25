@extends('layouts.app')
@section('title', 'Search · Meet with the Techies')
@section('content')
<main class="mx-auto max-w-7xl px-4 py-8 sm:py-12">
  <header class="mb-8 rounded-3xl bg-[#101E33] p-6 text-white sm:p-10">
    <p class="text-xs font-bold uppercase tracking-widest text-[#FFB4A4]">Explore the network</p>
    <h1 class="mt-2 font-display text-3xl font-extrabold">Search Meet with the Techies</h1>
    <form method="GET" action="{{ route('search') }}" role="search" class="mt-6 flex flex-col gap-3 sm:flex-row">
      <label for="search-query" class="sr-only">Search members, hubs, events, and opportunities</label>
      <input id="search-query" type="search" name="q" value="{{ $search }}" minlength="2" maxlength="100" required placeholder="Try a skill, city, event, or role" class="min-w-0 flex-1 rounded-xl border border-white/20 bg-white px-4 py-3 text-[#101E33] placeholder:text-slate-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
      <button class="rounded-xl bg-[#FF5C3D] px-6 py-3 font-semibold text-white hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Search</button>
    </form>
    @error('q')<p class="mt-2 text-sm text-red-200" role="alert">{{ $message }}</p>@enderror
  </header>

  @if($search === '')
    <section class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
      <h2 class="font-semibold text-[#101E33]">Search across the community</h2>
      <p class="mt-2 text-sm text-slate-600">Find public member profiles, verified hubs, published events, opportunities, and articles.</p>
    </section>
  @else
    <p class="mb-6 text-sm text-slate-600">Results for <strong class="text-[#101E33]">{{ $search }}</strong></p>
    <div class="space-y-8">
      @foreach([
        ['Members', $members],
        ['Hubs', $hubs],
        ['Events', $events],
        ['Opportunities', $opportunities],
        ['Articles', $articles],
      ] as [$heading, $results])
        @if($results->isNotEmpty())
          <section aria-label="{{ $heading }} search results">
            <h2 class="mb-3 font-display text-xl font-bold text-[#101E33]">{{ $heading }}</h2>
            <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
              @foreach($results as $result)
                @php
                  $url = match (class_basename($result)) {
                    'User' => $result->profile->publicUrl(),
                    'Hub' => route('hubs.show', $result->slug),
                    'Event' => route('events.show', $result->slug),
                    'Opportunity' => route('opportunities.show', $result->slug),
                    'Article' => route('articles.show', $result->slug),
                    default => route('search'),
                  };
                  $description = match (class_basename($result)) {
                    'User' => $result->profile->professional_title ?: $result->profile->technology_category,
                    'Hub' => $result->city ? collect([$result->city, $result->state])->filter()->implode(', ') : \Illuminate\Support\Str::limit(strip_tags((string) $result->description), 100),
                    'Event' => $result->start_date?->format('M j, Y'),
                    'Opportunity' => $result->organization ?: $result->location,
                    'Article' => $result->excerpt,
                    default => null,
                  };
                @endphp
                <li><a href="{{ $url }}" class="block h-full rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
                  <h3 class="font-semibold text-[#101E33]">{{ $result instanceof \App\Models\User ? $result->name : $result->title ?? $result->name }}</h3>
                  @if($description)<p class="mt-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags((string) $description), 120) }}</p>@endif
                </a></li>
              @endforeach
            </ul>
          </section>
        @endif
      @endforeach

      @if(collect([$members, $hubs, $events, $opportunities, $articles])->every(fn ($results) => $results->isEmpty()))
        <section class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
          <h2 class="font-semibold text-[#101E33]">No results found</h2>
          <p class="mt-2 text-sm text-slate-600">Try a broader phrase, a different spelling, or another city or skill.</p>
        </section>
      @endif
    </div>
  @endif
</main>
@endsection
