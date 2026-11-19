@extends('layouts.app')
@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
  $link = 'text-[#FF5C3D] font-medium hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
@endphp

<section class="bg-[#101E33] text-white py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-4">
    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-white/10 border border-white/20">News &amp; Updates</span>
    <h1 class="mt-5 text-4xl font-bold">What's happening across the network.</h1>
    <p class="mt-4 text-slate-300 max-w-2xl">Announcements, community updates and stories from MWTT chapters, hubs and members.</p>
  </div>
</section>

{{-- Announcements --}}
<section class="max-w-7xl mx-auto px-4 py-12 md:py-16" aria-labelledby="announce-h">
  <h2 id="announce-h" class="text-2xl font-bold text-[#101E33] mb-6">Announcements</h2>

  @if($announcements->count())
    <ul class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach($announcements as $announcement)
        @php
          $date = ($announcement->published_at ?? $announcement->created_at);
          $meta = array_filter([$date?->format('M j, Y'), $announcement->chapter?->name]);
          $body = trim((string) $announcement->body);
          $long = mb_strlen($body) > 220;
        @endphp
        <li>
          <article class="h-full bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
            @if($meta)
              <div class="text-xs text-[#C23A1F] font-medium">
                @if($date)<time datetime="{{ $date->toIso8601String() }}">{{ $date->format('M j, Y') }}</time>@endif
                @if($announcement->chapter?->name){{ $date ? ', ' : '' }}{{ $announcement->chapter->name }}@endif
              </div>
            @endif
            <h3 class="mt-2 text-lg font-bold text-[#101E33]">{{ $announcement->title }}</h3>

            @if($long)
              {{-- The card used to cut off at 220 characters with no way to read the rest --}}
              <details class="group mt-2">
                <summary class="list-none cursor-pointer {{ $focus }} rounded">
                  <p class="text-sm text-slate-700 leading-relaxed group-open:hidden">{{ \Illuminate\Support\Str::limit($body, 220) }}</p>
                  <span class="mt-2 inline-block text-sm {{ $link }} group-open:hidden">Read more</span>
                  <span class="mt-2 hidden text-sm {{ $link }} group-open:inline-block">Show less</span>
                </summary>
                <p class="mt-2 text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $body }}</p>
              </details>
            @else
              <p class="mt-2 text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $body }}</p>
            @endif
          </article>
        </li>
      @endforeach
    </ul>
  @else
    <div class="max-w-2xl mx-auto text-center bg-white rounded-2xl border border-slate-100 p-10 shadow-sm">
      <h3 class="text-xl font-bold text-[#101E33]">No announcements yet</h3>
      <p class="mt-2 text-slate-600">Community news will appear here as chapters and hubs share updates. Subscribe to be notified when something new is published.</p>
      <a href="{{ route('home') }}#newsletter" class="mt-6 inline-block px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition {{ $focus }}">Subscribe to updates</a>
    </div>
  @endif
</section>

{{-- Latest articles --}}
@if($articles->count())
<section class="max-w-7xl mx-auto px-4 pb-12 md:pb-16" aria-labelledby="articles-h">
  <div class="flex items-end justify-between gap-4 mb-6">
    <div>
      <h2 id="articles-h" class="text-2xl font-bold text-[#101E33]">Latest community articles</h2>
      <p class="text-slate-600 mt-1">Practical guides and stories written by MWTT members.</p>
    </div>
    <a href="{{ route('articles') }}" class="shrink-0 text-sm {{ $link }}">View all<span class="sr-only"> articles</span></a>
  </div>
  <ul class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($articles as $article)
      @php $artMeta = array_filter([$article->author?->name, $article->published_at?->format('M j, Y')]); @endphp
      <li>
        <a href="{{ route('articles.show', $article) }}" class="h-full block bg-white rounded-2xl border border-slate-100 p-6 shadow-sm hover:shadow-md transition {{ $focus }}">
          <div class="text-xs text-[#C23A1F] font-medium">{{ $article->topicLabel() }}</div>
          <h3 class="mt-2 text-lg font-bold text-[#101E33]">{{ $article->title }}</h3>
          <p class="mt-2 text-sm text-slate-700 leading-relaxed">{{ \Illuminate\Support\Str::limit($article->excerpt ?: strip_tags($article->body), 150) }}</p>
          @if($artMeta)<div class="mt-3 text-xs text-slate-500">{{ implode(', ', $artMeta) }}</div>@endif
        </a>
      </li>
    @endforeach
  </ul>
</section>
@endif

{{-- What we share --}}
<section class="bg-white py-12 md:py-16" aria-labelledby="share-h">
  <div class="max-w-7xl mx-auto px-4">
    <h2 id="share-h" class="text-2xl font-bold text-[#101E33]">What we share</h2>
    <ul class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach ([
        ['Community announcements', 'Important updates for members, chapters and hubs.'],
        ['Event recaps', 'Highlights from meetups, workshops and conferences.'],
        ['Opportunities', 'New jobs, internships, scholarships and grants.'],
        ['Chapter updates', 'What local chapters are building and planning.'],
        ['Hub stories', 'How technology hubs are supporting their communities.'],
        ['Member stories', 'Profiles and journeys from people in the network.'],
      ] as [$title, $blurb])
        <li class="rounded-2xl border border-slate-100 p-6">
          <h3 class="font-semibold text-[#101E33]">{{ $title }}</h3>
          <p class="mt-2 text-sm text-slate-600">{{ $blurb }}</p>
        </li>
      @endforeach
    </ul>
  </div>
</section>
@endsection