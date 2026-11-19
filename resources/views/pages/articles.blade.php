@extends('layouts.app')
@section('title', 'Community articles · Meet with the Techies')
@section('meta_description', 'Tutorials, career lessons, community stories and practical guides written by members of the Meet with the Techies community.')
@section('meta_keywords', 'tech articles, developer blog, Nigeria tech community, career advice, software engineering')
@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
  $chip = 'px-3 py-1.5 rounded-full text-sm border transition '.$focus;
  $chipOn = 'bg-[#101E33] text-white border-[#101E33]';
  $chipOff = 'border-slate-200 text-slate-700 bg-white hover:border-[#FF5C3D]';
  $filtered = $search || $activeTopic;
  // Keep the other filter when one changes
  $withSearch = fn (array $params) => route('articles', $params + ($search ? ['search' => $search] : []));
@endphp

<section class="bg-[#101E33] text-white py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-4">
    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-white/10 border border-white/20">Community articles</span>
    <h1 class="mt-5 text-4xl font-bold max-w-3xl">Practical knowledge, written by the community.</h1>
    <p class="mt-4 text-slate-300 max-w-2xl">Members share tutorials, career lessons and stories. Every article is reviewed before it is published.</p>
    <div class="mt-8 flex flex-col sm:flex-row gap-3">
      <form method="GET" action="{{ route('articles') }}" role="search" class="flex-1 flex gap-2">
        @if($activeTopic)<input type="hidden" name="topic" value="{{ $activeTopic }}">@endif
        <label for="article-search" class="sr-only">Search articles</label>
        <input id="article-search" type="search" name="search" value="{{ $search }}" placeholder="Search articles"
               class="w-full rounded-lg px-4 py-2.5 text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
        <button type="submit" class="px-5 py-2.5 bg-[#FF5C3D] rounded-lg font-semibold hover:opacity-90 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Search</button>
      </form>
      @auth
        <a href="{{ route('member.content.create') }}" class="px-5 py-2.5 border border-white/30 rounded-lg font-semibold hover:bg-white/10 transition text-center focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Write an article</a>
      @endauth
    </div>
  </div>
</section>

{{-- Topics --}}
<section class="max-w-7xl mx-auto px-4 pt-8 pb-6" aria-label="Filter by topic">
  <nav class="flex flex-wrap gap-2">
    <a href="{{ $withSearch([]) }}" @if(! $activeTopic) aria-current="page" @endif class="{{ $chip }} {{ ! $activeTopic ? $chipOn : $chipOff }}">All topics</a>
    @foreach($topics as $key => $topic)
      <a href="{{ $withSearch(['topic' => $key]) }}" @if($activeTopic === $key) aria-current="page" @endif class="{{ $chip }} {{ $activeTopic === $key ? $chipOn : $chipOff }}">{{ $topic['label'] }}</a>
    @endforeach
  </nav>

  @if($search)
    <div class="mt-4">
      <a href="{{ route('articles', $activeTopic ? ['topic' => $activeTopic] : []) }}"
         class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 rounded-full bg-[#FF5C3D]/10 text-[#C23A1F] text-sm hover:bg-[#FF5C3D]/20 {{ $focus }}">
        Search: {{ $search }}
        <span aria-hidden="true">&times;</span>
        <span class="sr-only">Clear search</span>
      </a>
    </div>
  @endif
</section>

{{-- Articles --}}
<section class="max-w-7xl mx-auto px-4 pb-16" aria-label="Articles">
  @if($articles->count())
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      @foreach($articles as $article)
        @php $meta = array_filter([$article->published_at?->format('M j, Y'), $article->readingTime().' min read']); @endphp
        <article class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden flex flex-col">
          {{-- Image link repeats the title link, so keep it out of keyboard and screen reader order --}}
          <a href="{{ route('articles.show', $article) }}" tabindex="-1" aria-hidden="true" class="block">
            @if($article->cover_image)
              <img src="{{ $article->cover_image }}" alt="" loading="lazy" class="w-full h-44 object-cover">
            @else
              <div class="w-full h-44 bg-[#101E33] flex items-center justify-center">
                <span class="text-white/80 font-bold text-2xl">{{ strtoupper(substr($article->title, 0, 1)) }}</span>
              </div>
            @endif
          </a>
          <div class="p-5 flex flex-col flex-1">
            <div class="text-xs text-[#C23A1F] font-medium">{{ $article->topicLabel() }}</div>
            <h2 class="mt-2 text-lg font-bold text-[#101E33] leading-snug">
              <a href="{{ route('articles.show', $article) }}" class="hover:text-[#FF5C3D] transition {{ $focus }} rounded">{{ $article->title }}</a>
            </h2>
            <p class="mt-2 text-sm text-slate-600 leading-relaxed flex-1">{{ \Illuminate\Support\Str::limit($article->excerpt ?: strip_tags($article->body), 150) }}</p>
            <div class="mt-4 flex flex-wrap items-center justify-between gap-x-3 gap-y-1 text-xs text-slate-500">
              @if($article->author?->name)<span>By {{ $article->author->name }}</span>@endif
              <span>{{ implode(', ', $meta) }}</span>
            </div>
          </div>
        </article>
      @endforeach
    </div>
    <nav class="mt-8" aria-label="Pagination">{{ $articles->withQueryString()->links() }}</nav>
  @else
    <div class="max-w-2xl mx-auto text-center bg-white rounded-2xl border border-slate-100 p-10 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33]">No articles found</h2>
      <p class="mt-2 text-slate-600">{{ $filtered ? 'Try a different search or topic.' : 'Published member articles will appear here.' }}</p>
      <div class="mt-6 flex flex-wrap justify-center gap-2">
        @if($filtered)
          <a href="{{ route('articles') }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm hover:bg-slate-50 {{ $focus }}">Clear filters</a>
        @endif
        @auth
          <a href="{{ route('member.content.create') }}" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm hover:opacity-90 transition {{ $focus }}">Write the first article</a>
        @else
          <a href="{{ route('register') }}" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm hover:opacity-90 transition {{ $focus }}">Join to write articles</a>
        @endauth
      </div>
    </div>
  @endif
</section>
@endsection