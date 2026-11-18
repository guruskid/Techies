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
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $article->title,
    'description' => $article->seo_description ?: $article->excerpt,
    'image' => $article->cover_image ? [$article->cover_image] : null,
    'datePublished' => $article->published_at?->toIso8601String(),
    'dateModified' => $article->updated_at->toIso8601String(),
    'author' => [
        '@type' => 'Person',
        'name' => $article->author?->name,
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'Meet with the Techies (MWTT)',
    ],
    'mainEntityOfPage' => route('articles.show', $article),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
@php
  $shareUrl = urlencode(route('articles.show', $article));
  $shareText = urlencode($article->title);
  $authorProfile = $article->author?->profile;
  $authorIsPublic = $authorProfile?->is_public && $authorProfile?->username;
@endphp

<section class="bg-[#101E33] text-white py-16">
  <div class="max-w-4xl mx-auto px-4">
    <a href="{{ route('articles', ['topic' => $article->topic]) }}" class="inline-block px-3 py-1 rounded-full text-xs bg-white/10 border border-white/20 hover:bg-white/15 transition">{{ $article->topicLabel() }}</a>
    <h1 class="mt-5 text-3xl md:text-5xl font-bold leading-tight">{{ $article->title }}</h1>
    <p class="mt-4 text-slate-300 text-lg max-w-3xl">{{ $article->excerpt }}</p>
    <div class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-400">
      <span class="flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-[#FF5C3D] text-white flex items-center justify-center text-xs font-bold">{{ strtoupper(substr($article->author?->name ?? 'M', 0, 1)) }}</span>
        {{ $article->author?->name }}
      </span>
      <span>{{ $article->published_at?->format('M j, Y') }}</span>
      <span>{{ $article->readingTime() }} min read</span>
      <span>{{ number_format($article->views) }} views</span>
    </div>
  </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-10 grid lg:grid-cols-3 gap-8">
  <article class="lg:col-span-2">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      @if($article->cover_image)
        <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full max-h-96 object-cover">
      @endif
      <div class="p-6 sm:p-10">
        <div class="text-slate-700 leading-relaxed text-lg whitespace-pre-line">{{ $article->body }}</div>

        <div class="mt-10 pt-6 border-t border-slate-100">
          <div class="text-sm font-semibold text-[#101E33] mb-3">Share this article</div>
          <div class="flex flex-wrap gap-2">
            <a href="https://x.com/intent/tweet?text={{ $shareText }}&amp;url={{ $shareUrl }}" target="_blank" rel="noopener" class="px-3 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition">Share on X</a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" class="px-3 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition">LinkedIn</a>
            <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener" class="px-3 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition">WhatsApp</a>
            <button type="button" x-data="{ copied: false }" @click="navigator.clipboard.writeText('{{ route('articles.show', $article) }}'); copied = true; setTimeout(() => copied = false, 2000)" class="px-3 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition">
              <span x-show="!copied">Copy link</span><span x-show="copied" x-cloak class="text-green-600">Copied!</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    @if($article->reviewer)
      <p class="mt-4 text-xs text-slate-400 text-center">Reviewed by {{ $article->reviewer->name }} on {{ $article->reviewed_at?->format('M j, Y') }}.</p>
    @endif
  </article>

  <aside class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
      <h2 class="font-bold text-[#101E33]">About the author</h2>
      <div class="mt-4 flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-[#101E33] text-white flex items-center justify-center font-bold">{{ strtoupper(substr($article->author?->name ?? 'M', 0, 1)) }}</div>
        <div>
          <div class="font-medium text-[#101E33]">{{ $article->author?->name }}</div>
          <div class="text-xs text-slate-500">{{ $authorProfile?->professional_title ?? 'MWTT member' }}</div>
        </div>
      </div>
      @if($authorProfile?->bio)
        <p class="mt-3 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($authorProfile->bio, 180) }}</p>
      @endif
      @if($authorIsPublic)
        <a href="{{ $authorProfile->publicUrl() }}" class="mt-4 inline-block text-sm text-[#FF5C3D] hover:underline">View full profile →</a>
      @endif
    </div>

    <div class="bg-[#101E33] text-slate-300 rounded-2xl p-6">
      <h2 class="font-bold text-white">Write for MWTT</h2>
      <p class="mt-2 text-sm">Share your experience with the community. Articles are reviewed before publishing.</p>
      @auth
        <a href="{{ route('member.content.create') }}" class="mt-4 inline-block px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition">Start writing</a>
      @else
        <a href="{{ route('register') }}" class="mt-4 inline-block px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition">Join to contribute</a>
      @endauth
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
      <h2 class="font-bold text-[#101E33]">Article details</h2>
      <dl class="mt-3 space-y-2 text-sm">
        <div class="flex justify-between gap-3"><dt class="text-slate-500">Topic</dt><dd>{{ $article->topicLabel() }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-slate-500">Published</dt><dd>{{ $article->published_at?->format('M j, Y') }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-slate-500">Reading time</dt><dd>{{ $article->readingTime() }} min</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-slate-500">Review</dt><dd>{{ $article->approvalLabel() }}</dd></div>
      </dl>
    </div>
  </aside>
</div>

@if($related->count())
<section class="bg-white py-16">
  <div class="max-w-7xl mx-auto px-4">
    <h2 class="text-2xl font-bold text-[#101E33]">Related articles</h2>
    <div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      @foreach($related as $item)
        <a href="{{ route('articles.show', $item) }}" class="block bg-slate-50 rounded-2xl p-6 hover:shadow-md transition">
          <div class="text-xs text-[#FF5C3D] font-medium">{{ $item->topicLabel() }}</div>
          <h3 class="mt-2 font-bold text-[#101E33]">{{ $item->title }}</h3>
          <p class="mt-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($item->excerpt, 110) }}</p>
          <div class="mt-3 text-xs text-slate-400">{{ $item->author?->name }} · {{ $item->published_at?->format('M j, Y') }}</div>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endif
@endsection
