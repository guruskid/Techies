@extends('layouts.app')
@section('title', ($article->seo_title ?: $article->title).' · Meet with the Techies')
@section('meta_description', $article->seo_description ?: ($article->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($article->body), 160)))
@section('meta_keywords', $article->seo_keywords ?: $article->topicLabel())
@section('canonical', $article->canonical_url ?: route('articles.show', $article))
@section('og_type', 'article')
@if($article->cover_image)
  @section('og_image', $article->cover_image)
@endif

@push('head')
@php
  // Drop empty values so the structured data never contains nulls
  $prune = function ($a) use (&$prune) {
    return array_filter(
      array_map(fn ($v) => is_array($v) ? $prune($v) : $v, $a),
      fn ($v) => $v !== null && $v !== '' && $v !== []
    );
  };
  $structuredData = $prune([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $article->title,
    'description' => $article->seo_description ?: $article->excerpt,
    'image' => $article->cover_image ? [$article->cover_image] : null,
    'datePublished' => $article->published_at?->toIso8601String(),
    'dateModified' => $article->updated_at->toIso8601String(),
    'author' => ['@type' => 'Person', 'name' => $article->author?->name],
    'publisher' => ['@type' => 'Organization', 'name' => 'Meet with the Techies (MWTT)'],
    'mainEntityOfPage' => route('articles.show', $article),
  ]);
@endphp
{{-- JSON_HEX_TAG stops a title like "</script>" from breaking out of this block --}}
<script type="application/ld+json">
{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>
@endpush

@section('content')
@php
  $articleUrl = route('articles.show', $article);
  $shareUrl = urlencode($articleUrl);
  $shareText = urlencode($article->title);
  $authorProfile = $article->author?->profile;
  $authorIsPublic = $authorProfile?->is_public && $authorProfile?->username;
  $authorName = $article->author?->name;
  $initial = strtoupper(substr($authorName ?? 'M', 0, 1));

  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
  $shareBtn = 'px-3 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition '.$focus;

  // Plain-text body split into paragraphs on blank lines (still escaped by {{ }})
  $paragraphs = array_values(array_filter(array_map('trim', preg_split('/\R{2,}/', (string) $article->body))));
@endphp

<style>[x-cloak]{display:none !important}</style>

<section class="bg-[#101E33] text-white py-12 md:py-16">
  <div class="max-w-4xl mx-auto px-4">
    <a href="{{ route('articles') }}" class="text-sm text-slate-300 hover:text-white hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-white rounded">Back to articles</a>
    <div class="mt-4">
      <a href="{{ route('articles', ['topic' => $article->topic]) }}" class="inline-block px-3 py-1 rounded-full text-xs bg-white/10 border border-white/20 hover:bg-white/15 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">{{ $article->topicLabel() }}</a>
    </div>
    <h1 class="mt-4 text-3xl md:text-5xl font-bold leading-tight">{{ $article->title }}</h1>
    @if($article->excerpt)<p class="mt-4 text-slate-300 text-lg max-w-3xl">{{ $article->excerpt }}</p>@endif
    <div class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-300">
      @if($authorName)
        <span class="flex items-center gap-2">
          @if($authorProfile?->avatar)
            <img src="{{ $authorProfile->avatar }}" alt="" class="w-8 h-8 rounded-full object-cover">
          @else
            <span class="w-8 h-8 rounded-full bg-[#FF5C3D] text-white flex items-center justify-center text-xs font-bold" aria-hidden="true">{{ $initial }}</span>
          @endif
          {{ $authorName }}
        </span>
      @endif
      @if($article->published_at)
        <time datetime="{{ $article->published_at->toIso8601String() }}">{{ $article->published_at->format('M j, Y') }}</time>
      @endif
      <span>{{ $article->readingTime() }} min read</span>
      <span>{{ number_format($article->views) }} {{ \Illuminate\Support\Str::plural('view', $article->views) }}</span>
    </div>
  </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-10 grid lg:grid-cols-3 gap-8 items-start">
  <article class="lg:col-span-2">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      @if($article->cover_image)
        <img src="{{ $article->cover_image }}" alt="" class="w-full max-h-96 object-cover">
      @endif
      <div class="p-6 sm:p-10">
        <div class="text-slate-800 leading-relaxed text-lg max-w-[68ch]">
          @foreach($paragraphs as $para)
            <p class="mb-5 last:mb-0 whitespace-pre-line">{{ $para }}</p>
          @endforeach
        </div>

        <div class="mt-10 pt-6 border-t border-slate-100" x-data="{ copied: false }">
          <h2 class="text-sm font-semibold text-[#101E33] mb-3">Share this article</h2>
          <div class="flex flex-wrap gap-2">
            <a href="https://x.com/intent/tweet?text={{ $shareText }}&amp;url={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="{{ $shareBtn }}">Share on X<span class="sr-only"> (opens in a new tab)</span></a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="{{ $shareBtn }}">Share on LinkedIn<span class="sr-only"> (opens in a new tab)</span></a>
            <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="{{ $shareBtn }}">Share on WhatsApp<span class="sr-only"> (opens in a new tab)</span></a>
            <button type="button"
                    @click="navigator.clipboard.writeText(@js($articleUrl)).then(() => { copied = true; setTimeout(() => copied = false, 2000) }).catch(() => {})"
                    class="{{ $shareBtn }}">
              <span x-show="!copied">Copy link</span><span x-show="copied" x-cloak class="text-green-700">Link copied</span>
            </button>
            <span class="sr-only" role="status" x-text="copied ? 'Link copied to clipboard' : ''"></span>
          </div>
        </div>
      </div>
    </div>
  </article>

  <aside class="space-y-6 lg:sticky lg:top-6" aria-label="About this article">
    <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6" aria-labelledby="author-h">
      <h2 id="author-h" class="font-bold text-[#101E33]">About the author</h2>
      <div class="mt-4 flex items-center gap-3">
        @if($authorProfile?->avatar)
          <img src="{{ $authorProfile->avatar }}" alt="" class="w-12 h-12 rounded-full object-cover">
        @else
          <div class="w-12 h-12 rounded-full bg-[#101E33] text-white flex items-center justify-center font-bold" aria-hidden="true">{{ $initial }}</div>
        @endif
        <div class="min-w-0">
          <div class="font-medium text-[#101E33]">{{ $authorName ?? 'MWTT member' }}</div>
          <div class="text-xs text-slate-600">{{ $authorProfile?->professional_title ?? 'MWTT member' }}</div>
        </div>
      </div>
      @if($authorProfile?->bio)
        <p class="mt-3 text-sm text-slate-700">{{ \Illuminate\Support\Str::limit($authorProfile->bio, 180) }}</p>
      @endif
      @if($authorIsPublic)
        <a href="{{ $authorProfile->publicUrl() }}" class="mt-4 inline-block text-sm font-medium text-[#FF5C3D] hover:underline {{ $focus }} rounded">View full profile</a>
      @endif
    </section>

    <section class="bg-[#101E33] text-slate-300 rounded-2xl p-6" aria-labelledby="write-h">
      <h2 id="write-h" class="font-bold text-white">Write for MWTT</h2>
      <p class="mt-2 text-sm">Share your experience with the community. Articles are reviewed before publishing.</p>
      @auth
        <a href="{{ route('member.content.create') }}" class="mt-4 inline-block px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Start writing</a>
      @else
        <a href="{{ route('register') }}" class="mt-4 inline-block px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Join to contribute</a>
      @endauth
    </section>

    {{-- Topic, date and reading time are already in the header, so only review details are here --}}
    <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6" aria-labelledby="review-h">
      <h2 id="review-h" class="font-bold text-[#101E33]">Review</h2>
      <dl class="mt-3 space-y-2 text-sm">
        <div class="flex justify-between gap-3"><dt class="text-slate-600">Status</dt><dd class="text-[#101E33] font-medium">{{ $article->approvalLabel() }}</dd></div>
        @if($article->reviewer)
          <div class="flex justify-between gap-3">
            <dt class="text-slate-600">Reviewed by</dt>
            <dd class="text-[#101E33] text-right">{{ $article->reviewer->name }}@if($article->reviewed_at)<span class="block text-xs text-slate-500">{{ $article->reviewed_at->format('M j, Y') }}</span>@endif</dd>
          </div>
        @endif
      </dl>
    </section>
  </aside>
</div>

@if($related->count())
<section class="bg-white py-12 md:py-16" aria-labelledby="related-h">
  <div class="max-w-7xl mx-auto px-4">
    <h2 id="related-h" class="text-2xl font-bold text-[#101E33]">Related articles</h2>
    <ul class="mt-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      @foreach($related as $item)
        @php $relMeta = array_filter([$item->author?->name, $item->published_at?->format('M j, Y')]); @endphp
        <li>
          <a href="{{ route('articles.show', $item) }}" class="h-full block bg-slate-50 rounded-2xl p-6 hover:shadow-md transition {{ $focus }}">
            <div class="text-xs text-[#C23A1F] font-medium">{{ $item->topicLabel() }}</div>
            <h3 class="mt-2 font-bold text-[#101E33]">{{ $item->title }}</h3>
            @if($item->excerpt)<p class="mt-2 text-sm text-slate-700">{{ \Illuminate\Support\Str::limit($item->excerpt, 110) }}</p>@endif
            @if($relMeta)<div class="mt-3 text-xs text-slate-500">{{ implode(', ', $relMeta) }}</div>@endif
          </a>
        </li>
      @endforeach
    </ul>
  </div>
</section>
@endif
@endsection