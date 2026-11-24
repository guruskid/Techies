@extends('layouts.admin')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 pb-12">
  <div class="mb-6">
    <h1 class="font-display text-2xl font-extrabold text-[#101E33]">Member feedback</h1>
    <p class="mt-1 text-sm text-slate-500">Thirty-day feedback from active members. Average rating: <strong class="text-[#101E33]">{{ $average ?: '—' }}</strong></p>
  </div>

  <div class="space-y-4">
    @forelse($feedback as $entry)
      <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <p class="font-medium text-[#101E33]">{{ $entry->user?->name }}</p>
            <p class="text-xs text-slate-500">{{ $entry->user?->email }} · {{ $entry->submitted_at?->format('M j, Y') }}</p>
          </div>
          <span class="rounded-full bg-[#FF5C3D]/10 px-2.5 py-1 text-xs font-semibold text-[#C23A1F]">{{ $entry->rating }} / 5</span>
        </div>
        @if($entry->comments)
          <p class="mt-3 text-sm text-slate-600 whitespace-pre-line">{{ $entry->comments }}</p>
        @endif
      </article>
    @empty
      <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">No feedback yet.</div>
    @endforelse
  </div>
  <div class="mt-4">{{ $feedback->links() }}</div>
</div>
@endsection