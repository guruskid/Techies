@extends('layouts.app')
@section('title', $cohort->title.' · Cohorts')
@section('content')
@include('partials.member-nav')
<div class="max-w-5xl mx-auto px-4 py-8 pb-14">
  <a href="{{ route('cohorts.index') }}" class="text-sm font-medium text-[#C23A1F] hover:underline">All cohorts</a>
  <div class="mt-4 grid md:grid-cols-[1.2fr_0.8fr] gap-6 items-start">
    <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <p class="text-[11px] font-semibold uppercase tracking-wider text-[#C23A1F]">{{ $cohort->track ?: 'Cohort' }}</p>
      <h1 class="mt-2 font-display text-3xl font-extrabold text-[#101E33]">{{ $cohort->title }}</h1>
      <p class="mt-2 text-sm text-slate-500">{{ $cohort->starts_at?->format('M j, Y') }} – {{ $cohort->ends_at?->format('M j, Y') }}</p>
      @if($cohort->description)
        <div class="prose-tech mt-6 max-w-none">{!! \App\Support\RichText::render($cohort->description) !!}</div>
      @endif
    </article>
    <aside class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <p class="text-sm text-slate-500">Members joined</p>
      <p class="mt-1 font-display text-3xl font-extrabold text-[#101E33]">{{ $cohort->members_count }}{{ $cohort->capacity ? ' / '.$cohort->capacity : '' }}</p>
      @if($joined)
        <form method="POST" action="{{ route('cohorts.leave', $cohort) }}" class="mt-5">@csrf
          <button class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Leave cohort</button>
        </form>
      @elseif($cohort->isOpen())
        <form method="POST" action="{{ route('cohorts.join', $cohort) }}" class="mt-5">@csrf
          <button class="w-full rounded-lg bg-[#FF5C3D] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#e94f32]">Join cohort</button>
        </form>
      @else
        <p class="mt-5 rounded-lg bg-slate-50 p-3 text-sm text-slate-600">This cohort is closed.</p>
      @endif
    </aside>
  </div>
</div>
@endsection