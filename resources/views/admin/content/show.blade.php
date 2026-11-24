@extends('layouts.admin')
@section('content')
@php
  $canReview = $article->canBeApprovedBy(auth()->user());
  $statusStyles = [
    'draft' => 'bg-slate-100 text-slate-600',
    'pending' => 'bg-amber-50 text-amber-700',
    'approved' => 'bg-green-50 text-green-700',
    'rejected' => 'bg-red-50 text-red-700',
  ];
@endphp
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
    <div>
      <a href="{{ route('admin.content.index') }}" class="text-sm text-[#FF5C3D] hover:underline">← Back to review queue</a>
      <h1 class="mt-2 text-2xl font-bold text-[#101E33]">{{ $article->title }}</h1>
      <p class="mt-1 text-sm text-slate-500">
        By {{ $article->author?->name }} · {{ $article->topicLabel() }} · Updated {{ $article->updated_at->diffForHumans() }}
      </p>
    </div>
    <div class="flex flex-wrap gap-2">
      <span class="px-3 py-1.5 rounded-full text-xs font-medium {{ $statusStyles[$article->status] ?? 'bg-slate-100 text-slate-600' }}">{{ $article->statusLabel() }}</span>
      <span class="px-3 py-1.5 rounded-full text-xs {{ $article->requiresSuperAdminApproval() ? 'bg-[#101E33] text-white' : 'bg-[#FF5C3D]/10 text-[#FF5C3D]' }}">{{ $article->approvalLabel() }}</span>
      @if($article->isPublished())
        <a href="{{ route('articles.show', $article) }}" class="px-3 py-1.5 rounded-full text-xs border border-slate-200 hover:bg-white">View live</a>
      @endif
    </div>
  </div>

  @unless($canReview)
    <div class="mb-6 rounded-xl bg-amber-50 border border-amber-100 p-4 text-sm text-amber-800">
      This topic requires a super administrator. You can read the submission, but you cannot approve or reject it.
    </div>
  @endunless

  <div class="grid lg:grid-cols-3 gap-6">
    <article class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
      @if($article->cover_image)
        <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-56 object-cover rounded-xl mb-6">
      @endif
      <div class="prose max-w-none">
        <p class="text-lg text-slate-600 leading-relaxed">{{ $article->excerpt }}</p>
        <div class="mt-6 text-slate-700 leading-relaxed whitespace-pre-line">{{ $article->body }}</div>
      </div>

      @if($article->review_notes)
        <div class="mt-8 rounded-xl bg-slate-50 border border-slate-100 p-4 text-sm">
          <div class="font-semibold text-[#101E33]">Latest reviewer feedback</div>
          <p class="mt-1 text-slate-600">{{ $article->review_notes }}</p>
          @if($article->reviewer)
            <p class="mt-2 text-xs text-slate-400">— {{ $article->reviewer->name }}, {{ $article->reviewed_at?->format('M j, Y g:ia') }}</p>
          @endif
        </div>
      @endif
    </article>

    <aside class="space-y-6">
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-center justify-between">
          <h2 class="font-bold text-[#101E33]">SEO score</h2>
          <span class="text-lg font-bold {{ $article->seoScore() >= 70 ? 'text-green-600' : ($article->seoScore() >= 50 ? 'text-amber-600' : 'text-red-500') }}">{{ $article->seoScore() }}%</span>
        </div>
        <div class="mt-3 h-2 bg-slate-100 rounded-full overflow-hidden">
          <div class="h-full {{ $article->seoScore() >= 70 ? 'bg-green-500' : ($article->seoScore() >= 50 ? 'bg-amber-500' : 'bg-red-400') }}" style="width: {{ $article->seoScore() }}%"></div>
        </div>
        <ul class="mt-4 space-y-2">
          @foreach($article->seoChecklist() as $check)
            <li class="flex items-start gap-2 text-sm {{ $check['passed'] ? 'text-green-700' : 'text-slate-600' }}">
              <span class="mt-0.5 w-4 h-4 rounded-full flex items-center justify-center text-[10px] {{ $check['passed'] ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-400' }}">{{ $check['passed'] ? '✓' : '•' }}</span>
              <span>{{ $check['label'] }}</span>
            </li>
          @endforeach
        </ul>
      </div>

      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-2 text-sm">
        <h2 class="font-bold text-[#101E33] mb-2">Metadata</h2>
        <div class="flex justify-between gap-3"><span class="text-slate-500">Slug</span><span class="text-right break-all">{{ $article->slug }}</span></div>
        <div class="flex justify-between gap-3"><span class="text-slate-500">Reading time</span><span>{{ $article->readingTime() }} min</span></div>
        <div class="flex justify-between gap-3"><span class="text-slate-500">Submitted</span><span>{{ $article->submitted_at?->format('M j, Y') ?? '—' }}</span></div>
        <div class="flex justify-between gap-3"><span class="text-slate-500">Published</span><span>{{ $article->published_at?->format('M j, Y') ?? '—' }}</span></div>
        @if($article->seo_title)
          <div class="pt-2"><span class="text-slate-500">SEO title</span><p class="mt-1">{{ $article->seo_title }}</p></div>
        @endif
        @if($article->seo_description)
          <div class="pt-2"><span class="text-slate-500">Meta description</span><p class="mt-1">{{ $article->seo_description }}</p></div>
        @endif
        @if($article->seo_keywords)
          <div class="pt-2"><span class="text-slate-500">Keywords</span><p class="mt-1">{{ $article->seo_keywords }}</p></div>
        @endif
      </div>

      @if($canReview)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
          <h2 class="font-bold text-[#101E33]">Review decision</h2>

          @if(in_array($article->status, ['pending', 'rejected'], true))
            <form method="POST" action="{{ route('admin.content.approve', $article) }}" class="space-y-2">
              @csrf
              <textarea name="review_notes" rows="2" placeholder="Optional note for the author" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"></textarea>
              <button class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition">Approve &amp; publish</button>
            </form>

            <form method="POST" action="{{ route('admin.content.reject', $article) }}" class="space-y-2 pt-3 border-t border-slate-100">
              @csrf
              <label class="block text-sm font-medium text-slate-700">Request changes</label>
              <textarea name="review_notes" rows="3" required placeholder="Tell the author what to fix" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"></textarea>
              <button class="w-full px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-semibold hover:bg-red-50 transition">Send back for changes</button>
            </form>
          @endif

          @if($article->status === 'approved')
            <form method="POST" action="{{ route('admin.content.unpublish', $article) }}" class="space-y-2">
              @csrf
              <textarea name="review_notes" rows="2" placeholder="Reason for unpublishing (optional)" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"></textarea>
              <button class="w-full px-4 py-2 border border-amber-200 text-amber-700 rounded-lg text-sm font-semibold hover:bg-amber-50 transition">Unpublish article</button>
            </form>
          @endif

          @if(auth()->user()->isSuperAdmin() && $article->status !== 'approved')
            <form method="POST" action="{{ route('super-admin.content.force-publish', $article) }}" class="pt-3 border-t border-slate-100" onsubmit="return confirm('Force publish this article without the normal review flow?')">
              @csrf
              <button class="w-full px-4 py-2 bg-[#101E33] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition">Force publish (super admin)</button>
            </form>
          @endif
        </div>
      @endif
    </aside>
  </div>
</div>
@endsection
