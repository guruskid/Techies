@extends('layouts.app')
@section('title', 'Cohorts · Meet with the Techies')
@section('content')
@include('partials.member-nav')
<div class="max-w-7xl mx-auto px-4 py-8 pb-14">
  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#C23A1F]">Learn together</p>
  <h1 class="mt-2 font-display text-2xl md:text-3xl font-extrabold text-[#101E33]">Cohorts and build sprints</h1>
  <p class="mt-1 text-sm text-slate-500">Short, structured programmes with weekly tasks, peer check-ins and a demo day.</p>

  <div class="mt-6 grid md:grid-cols-2 gap-4">
    @forelse($cohorts as $cohort)
      <a href="{{ route('cohorts.show', $cohort->slug) }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-[#FF5C3D]/40">
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#C23A1F]">{{ $cohort->track ?: 'Cohort' }}</p>
            <h2 class="mt-1 font-display text-xl font-bold text-[#101E33]">{{ $cohort->title }}</h2>
          </div>
          <span class="rounded-full px-2 py-1 text-xs {{ $cohort->status === 'open' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($cohort->status) }}</span>
        </div>
        <p class="mt-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags((string) $cohort->description), 150) }}</p>
        <p class="mt-4 text-xs text-slate-500">{{ $cohort->starts_at?->format('M j') }} – {{ $cohort->ends_at?->format('M j, Y') }} · {{ $cohort->members_count }} joined</p>
      </a>
    @empty
      <p class="text-slate-500">No cohorts are open yet.</p>
    @endforelse
  </div>
  <div class="mt-4">{{ $cohorts->links() }}</div>
</div>
@endsection