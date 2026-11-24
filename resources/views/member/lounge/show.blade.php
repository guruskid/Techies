@extends('layouts.app')
@section('title', $post->title.' · Member lounge')
@section('content')
@include('partials.member-nav')
<div class="max-w-4xl mx-auto px-4 py-8 pb-14">
  <a href="{{ route('lounge.index') }}" class="text-sm font-medium text-[#C23A1F] hover:underline">Back to lounge</a>
  <article class="mt-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#C23A1F]">{{ $post->category }}</p>
    <h1 class="mt-2 font-display text-3xl font-extrabold text-[#101E33]">{{ $post->title }}</h1>
    <p class="mt-1 text-xs text-slate-500">By {{ $post->user?->name }} · {{ $post->created_at?->format('M j, Y') }}</p>
    <div class="mt-5 whitespace-pre-line text-slate-700 leading-relaxed">{{ $post->body }}</div>
  </article>

  <section class="mt-6">
    <h2 class="font-display text-lg font-bold text-[#101E33]">Replies ({{ $post->replies->count() }})</h2>
    <div class="mt-3 space-y-3">
      @foreach($post->replies as $reply)
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-sm font-medium text-[#101E33]">{{ $reply->user?->name }}</p>
          <p class="text-xs text-slate-500">{{ $reply->created_at?->diffForHumans() }}</p>
          <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $reply->body }}</p>
        </div>
      @endforeach
    </div>

    <form method="POST" action="{{ route('lounge.reply', $post) }}" class="mt-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
      @csrf
      <label for="body" class="sr-only">Reply</label>
      <textarea id="body" name="body" rows="3" required class="w-full rounded-lg border border-slate-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]" placeholder="Add a reply"></textarea>
      <button class="mt-2 rounded-lg bg-[#101E33] px-4 py-2 text-sm font-semibold text-white">Post reply</button>
    </form>
  </section>
</div>
@endsection