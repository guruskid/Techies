@extends('layouts.admin')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-8 pb-12">
  <div class="mb-6"><h1 class="font-display text-2xl font-extrabold text-[#101E33]">Member lounge</h1><p class="mt-1 text-sm text-slate-500">Moderate member conversations.</p></div>
  <div class="space-y-3">
    @forelse($posts as $post)
      <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-[#C23A1F]">{{ $post->category }}</p>
            <p class="font-medium text-[#101E33]">{{ $post->title }}</p>
            <p class="text-xs text-slate-500">By {{ $post->user?->name }} · {{ $post->replies_count }} replies</p>
          </div>
          <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('admin.lounge.pin', $post) }}">@csrf<button class="text-sm text-[#C23A1F] hover:underline">{{ $post->is_pinned ? 'Unpin' : 'Pin' }}</button></form>
            <form method="POST" action="{{ route('admin.lounge.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">@csrf @method('DELETE')<button class="text-sm text-red-600 hover:underline">Delete</button></form>
          </div>
        </div>
      </article>
    @empty
      <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">No lounge posts yet.</div>
    @endforelse
  </div>
  <div class="mt-4">{{ $posts->links() }}</div>
</div>
@endsection