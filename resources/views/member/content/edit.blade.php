@extends('layouts.app')
@section('content')
@include('partials.member-nav')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
      <a href="{{ route('member.content.index') }}" class="text-sm text-[#FF5C3D] hover:underline">← Back to my content</a>
      <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Edit article</h1>
      <p class="mt-1 text-slate-500">{{ $article->title }}</p>
    </div>
    @if($article->isPublished())
      <a href="{{ route('articles.show', $article) }}" class="px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-white transition">View live article</a>
    @endif
  </div>

  @if($article->status === 'rejected' && $article->review_notes)
    <div class="mb-6 rounded-xl bg-red-50 border border-red-100 p-4 text-sm text-red-700">
      <p class="font-semibold">Changes requested</p>
      <p class="mt-1">{{ $article->review_notes }}</p>
    </div>
  @endif

  @if($article->status === 'pending')
    <div class="mb-6 rounded-xl bg-amber-50 border border-amber-100 p-4 text-sm text-amber-700">
      This article is awaiting {{ $article->requiresSuperAdminApproval() ? 'super admin' : 'admin' }} review. Editing it now will return it to draft.
    </div>
  @endif

  @if($errors->any())
    <div class="mb-6 rounded-xl bg-red-50 border border-red-100 p-4 text-sm text-red-700">
      <p class="font-semibold">Please fix the following:</p>
      <ul class="mt-1 list-disc list-inside">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @include('member.content.form')
</div>
@endsection
