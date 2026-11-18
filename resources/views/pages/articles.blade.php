@extends('layouts.app')
@section('title', 'Community articles · Meet with the Techies')
@section('meta_description', 'Tutorials, career lessons, community stories and practical guides written by members of the Meet with the Techies community.')
@section('meta_keywords', 'tech articles, developer blog, Nigeria tech community, career advice, software engineering')
@section('content')
<section class="bg-[#101E33] text-white py-16">
  <div class="max-w-7xl mx-auto px-4">
    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-white/10 border border-white/20">Community articles</span>
    <h1 class="mt-5 text-4xl font-bold max-w-3xl">Practical knowledge, written by the community.</h1>
    <p class="mt-4 text-slate-300 max-w-2xl">Members share tutorials, career lessons and stories. Every article is reviewed before it is published.</p>
    <div class="mt-8 flex flex-col sm:flex-row gap-3">
      <form method="GET" action="{{ route('articles') }}" class="flex-1 flex gap-2">
        @if($activeTopic)
          <input type="hidden" name="topic" value="{{ $activeTopic }}">
        @endif
        <input name="search" value="{{ $search }}" placeholder="Search articles" class="w-full rounded-lg px-4 py-2.5 text-slate-800 bg-white">
        <button class="px-5 py-2.5 bg-[#FF5C3D] rounded-lg font-semibold hover:opacity-90 transition">Search</button>
      </form>
      @auth
        <a href="{{ route('member.content.create') }}" class="px-5 py-2.5 border border-white/30 rounded-lg font-semibold hover:bg-white/10 transition text-center">Write an article</a>
      @endauth
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('articles') }}" class="px-3 py-1.5 rounded-full text-sm border {{ ! $activeTopic ? 'bg-[#101E33] text-white border-[#101E33]' : 'border-slate-200 text-slate-600 hover:border-[#FF5C3D]' }}">All topics</a>
    @foreach($topics as $key => $topic)
      <a href="{{ route('articles', ['topic' => $key] + ($search ? ['search' => $search] : [])) }}" class="px-3 py-1.5 rounded-full text-sm border {{ $activeTopic === $key ? 'bg-[#101E33] text-white border-[#101E33]' : 'border-slate-200 text-slate-600 hover:border-[#FF5C3D]' }}">{{ $topic['label'] }}</a>
    @endforeach
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 pb-16">
  @if($articles->count())
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      @foreach($articles as $article)
        <article class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">
          <a href="{{ route('articles.show', $article) }}" class="block">
            @if($article->cover_image)
              <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" loading="lazy" class="w-full h-44 object-cover">
            @else
              <div class="w-full h-44 bg-[#101E33] flex items-center justify-center">
                <span class="text-white/80 font-bold text-2xl">{{ strtoupper(substr($article->title, 0, 1)) }}</span>
              </div>
            @endif
          </a>
          <div class="p-5 flex flex-col flex-1">
            <div class="text-xs text-[#FF5C3D] font-medium">{{ $article->topicLabel() }}</div>
            <h2 class="mt-2 text-lg font-bold text-[#101E33] leading-snug">
              <a href="{{ route('articles.show', $article) }}" class="hover:text-[#FF5C3D] transition">{{ $article->title }}</a>
            </h2>
            <p class="mt-2 text-sm text-slate-600 leading-relaxed flex-1">{{ \Illuminate\Support\Str::limit($article->excerpt ?: strip_tags($article->body), 150) }}</p>
            <div class="mt-4 flex items-center justify-between text-xs text-slate-400">
              <span>{{ $article->author?->name }}</span>
              <span>{{ $article->published_at?->format('M j, Y') }} · {{ $article->readingTime() }} min read</span>
            </div>
          </div>
        </article>
      @endforeach
    </div>
    <div class="mt-8">{{ $articles->links() }}</div>
  @else
    <div class="max-w-2xl mx-auto text-center bg-white rounded-2xl border border-slate-100 p-10 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33]">No articles found</h2>
      <p class="mt-2 text-slate-600">{{ $search || $activeTopic ? 'Try a different search or topic.' : 'Published member articles will appear here.' }}</p>
      <div class="mt-6 flex flex-wrap justify-center gap-2">
        <a href="{{ route('articles') }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm">Clear filters</a>
        @auth
          <a href="{{ route('member.content.create') }}" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm">Write the first article</a>
        @endauth
      </div>
    </div>
  @endif
</section>
@endsection
