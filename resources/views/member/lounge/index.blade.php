@extends('layouts.app')
@section('title', 'Member lounge · Meet with the Techies')
@section('content')
@include('partials.member-nav')
<div class="max-w-6xl mx-auto px-4 py-8 pb-14">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
      <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#C23A1F]">Members only</p>
      <h1 class="mt-2 font-display text-2xl md:text-3xl font-extrabold text-[#101E33]">Member lounge</h1>
      <p class="mt-1 text-sm text-slate-500">Ask questions, share wins and find collaborators between events.</p>
    </div>
    <a href="{{ route('lounge.create') }}" class="rounded-lg bg-[#FF5C3D] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#e94f32]">New post</a>
  </div>

  <div class="mt-6 space-y-3">
    @forelse($posts as $post)
      <a href="{{ route('lounge.show', $post) }}" class="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-[#FF5C3D]/40">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#C23A1F]">{{ $post->category }}</p>
            <h2 class="mt-1 font-display text-lg font-bold text-[#101E33]">{{ $post->title }}</h2>
            <p class="text-xs text-slate-500">By {{ $post->user?->name }} · {{ $post->created_at?->diffForHumans() }}</p>
          </div>
          <span class="text-xs text-slate-500">{{ $post->replies_count }} {{ \Illuminate\Support\Str::plural('reply', $post->replies_count) }}</span>
        </div>
        <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($post->body, 220) }}</p>
      </a>
    @empty
      <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">No posts yet. Start the first conversation.</div>
    @endforelse
  </div>
  <div class="mt-4">{{ $posts->links() }}</div>
</div>
@endsection