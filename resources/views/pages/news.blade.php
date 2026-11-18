@extends('layouts.app')
@section('content')
<section class="bg-[#101E33] text-white py-16">
  <div class="max-w-7xl mx-auto px-4">
    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-white/10 border border-white/20">News & Updates</span>
    <h1 class="mt-5 text-4xl font-bold">What's happening across the network.</h1>
    <p class="mt-4 text-slate-300 max-w-2xl">Announcements, community updates and stories from MWTT chapters, hubs and members.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-16">
  @if($announcements->count())
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach($announcements as $announcement)
        <article class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
          <div class="text-xs text-[#FF5C3D] font-medium">
            {{ $announcement->published_at?->format('M j, Y') ?? $announcement->created_at?->format('M j, Y') }}
            @if($announcement->chapter) · {{ $announcement->chapter->name }} @endif
          </div>
          <h2 class="mt-2 text-lg font-bold text-[#101E33]">{{ $announcement->title }}</h2>
          <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ \Illuminate\Support\Str::limit($announcement->body, 220) }}</p>
        </article>
      @endforeach
    </div>
  @else
    <div class="max-w-2xl mx-auto text-center bg-white rounded-2xl border border-slate-100 p-10 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33]">No announcements yet</h2>
      <p class="mt-2 text-slate-600">Community news will appear here as chapters and hubs share updates. Subscribe to be notified when something new is published.</p>
      <a href="{{ route('home') }}#newsletter" class="mt-6 inline-block px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Subscribe to updates</a>
    </div>
  @endif
</section>

@if($articles->count())
<section class="max-w-7xl mx-auto px-4 pb-16">
  <div class="flex items-end justify-between gap-4 mb-6">
    <div>
      <h2 class="text-2xl font-bold text-[#101E33]">Latest community articles</h2>
      <p class="text-slate-500 mt-1">Practical guides and stories written by MWTT members.</p>
    </div>
    <a href="{{ route('articles') }}" class="text-sm text-[#FF5C3D] hover:underline">View all articles →</a>
  </div>
  <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($articles as $article)
      <a href="{{ route('articles.show', $article) }}" class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm hover:shadow-md transition">
        <div class="text-xs text-[#FF5C3D] font-medium">{{ $article->topicLabel() }}</div>
        <h3 class="mt-2 text-lg font-bold text-[#101E33]">{{ $article->title }}</h3>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ \Illuminate\Support\Str::limit($article->excerpt ?: strip_tags($article->body), 150) }}</p>
        <div class="mt-3 text-xs text-slate-400">{{ $article->author?->name }} · {{ $article->published_at?->format('M j, Y') }}</div>
      </a>
    @endforeach
  </div>
</section>
@endif

<section class="bg-white py-16">
  <div class="max-w-7xl mx-auto px-4">
    <h2 class="text-2xl font-bold text-[#101E33]">What we share</h2>
    <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach ([
        ['Community announcements', 'Important updates for members, chapters and hubs.'],
        ['Event recaps', 'Highlights from meetups, workshops and conferences.'],
        ['Opportunities', 'New jobs, internships, scholarships and grants.'],
        ['Chapter updates', 'What local chapters are building and planning.'],
        ['Hub stories', 'How technology hubs are supporting their communities.'],
        ['Member stories', 'Profiles and journeys from people in the network.'],
      ] as $item)
        <div class="rounded-2xl border border-slate-100 p-6">
          <h3 class="font-semibold text-[#101E33]">{{ $item[0] }}</h3>
          <p class="mt-2 text-sm text-slate-500">{{ $item[1] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endsection
