@extends('layouts.app')
@section('content')
@php
  $statusStyles = [
    'draft' => 'bg-slate-100 text-slate-600',
    'pending' => 'bg-amber-50 text-amber-700',
    'approved' => 'bg-green-50 text-green-700',
    'rejected' => 'bg-red-50 text-red-700',
  ];
@endphp
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="bg-[#101E33] text-white rounded-3xl p-8 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <span class="inline-block px-3 py-1 rounded-full text-xs bg-white/10 border border-white/20">Member content</span>
      <h1 class="mt-4 text-2xl md:text-3xl font-bold">My Content</h1>
      <p class="mt-2 text-slate-300 max-w-2xl">Write articles, optimise them for search, and submit them for review. Admin or super admin approval depends on the topic you choose.</p>
    </div>
    <a href="{{ route('member.content.create') }}" class="inline-block px-5 py-2.5 bg-[#FF5C3D] rounded-lg font-semibold hover:opacity-90 transition">Write an article</a>
  </div>

  <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-8">
    @foreach([
      ['Total', $counts['total'], 'text-[#101E33]'],
      ['Drafts', $counts['drafts'], 'text-slate-600'],
      ['Pending review', $counts['pending'], 'text-amber-600'],
      ['Approved', $counts['approved'], 'text-green-600'],
      ['Changes requested', $counts['rejected'], 'text-red-600'],
    ] as [$label, $value, $colour])
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="text-xs text-slate-500">{{ $label }}</div>
        <div class="mt-1 text-2xl font-bold {{ $colour }}">{{ $value }}</div>
      </div>
    @endforeach
  </div>

  <div class="space-y-4">
    @forelse($articles as $article)
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2 text-xs">
              <span class="px-2.5 py-1 rounded-full font-medium {{ $statusStyles[$article->status] ?? 'bg-slate-100 text-slate-600' }}">{{ $article->statusLabel() }}</span>
              <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">{{ $article->topicLabel() }}</span>
              <span class="px-2.5 py-1 rounded-full {{ $article->requiresSuperAdminApproval() ? 'bg-[#101E33] text-white' : 'bg-[#FF5C3D]/10 text-[#FF5C3D]' }}">{{ $article->approvalLabel() }}</span>
            </div>
            <h2 class="mt-3 text-lg font-bold text-[#101E33]">{{ $article->title }}</h2>
            <p class="mt-1 text-sm text-slate-500">
              Updated {{ $article->updated_at->diffForHumans() }}
              @if($article->submitted_at) · Submitted {{ $article->submitted_at->diffForHumans() }} @endif
              @if($article->isPublished()) · {{ number_format($article->views) }} views @endif
            </p>
            @if($article->status === 'rejected' && $article->review_notes)
              <div class="mt-3 rounded-xl bg-red-50 border border-red-100 p-3 text-sm text-red-700">
                <span class="font-semibold">Reviewer feedback:</span> {{ $article->review_notes }}
              </div>
            @endif
            @if($article->status === 'pending')
              <div class="mt-3 rounded-xl bg-amber-50 border border-amber-100 p-3 text-sm text-amber-700">
                Waiting for {{ $article->requiresSuperAdminApproval() ? 'super admin' : 'admin' }} review. You will see feedback here if changes are requested.
              </div>
            @endif
          </div>

          <div class="flex flex-wrap items-center gap-2 shrink-0">
            @if($article->isEditableBy(auth()->user()))
              <a href="{{ route('member.content.edit', $article) }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition">Edit</a>
              <form method="POST" action="{{ route('member.content.submit', $article) }}">
                @csrf
                <button class="px-3 py-2 text-sm bg-[#FF5C3D] text-white rounded-lg hover:opacity-90 transition">Submit for review</button>
              </form>
            @endif
            @if($article->isPublished())
              <a href="{{ route('articles.show', $article) }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition">View live</a>
            @endif
            @if(in_array($article->status, ['draft', 'rejected'], true))
              <form method="POST" action="{{ route('member.content.destroy', $article) }}" onsubmit="return confirm('Delete this article? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button class="px-3 py-2 text-sm text-red-600 border border-red-100 rounded-lg hover:bg-red-50 transition">Delete</button>
              </form>
            @endif
          </div>
        </div>

        <div class="mt-4 flex items-center gap-3">
          <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full {{ $article->seoScore() >= 70 ? 'bg-green-500' : ($article->seoScore() >= 50 ? 'bg-amber-500' : 'bg-red-400') }}" style="width: {{ $article->seoScore() }}%"></div>
          </div>
          <span class="text-xs text-slate-500">SEO {{ $article->seoScore() }}%</span>
        </div>
      </div>
    @empty
      <div class="bg-white rounded-2xl border border-slate-100 p-10 text-center shadow-sm">
        <h2 class="text-xl font-bold text-[#101E33]">You have not written anything yet</h2>
        <p class="mt-2 text-slate-600 max-w-xl mx-auto">Share a tutorial, a career lesson, a community story or an idea that helps other techies. Choose a topic, add SEO details and submit it for review.</p>
        <a href="{{ route('member.content.create') }}" class="mt-6 inline-block px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Write your first article</a>
      </div>
    @endforelse
  </div>

  <div class="mt-6">{{ $articles->links() }}</div>
</div>
@endsection
