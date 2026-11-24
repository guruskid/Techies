@extends('layouts.app')
@section('title', 'Member programmes · Meet with the Techies')
@section('meta_description', 'Career clinics, office hours and mentorship programmes for members of Meet with the Techies.')

@section('content')
@include('partials.page-hero', [
  'kicker' => 'Membership programmes',
  'title' => 'Support that happens every month.',
  'subtitle' => 'Career clinics, office hours and mentorship designed to turn membership into progress.',
  'actions' => [
    ['label' => 'Join mentorship', 'url' => route('mentorship.index'), 'style' => 'primary'],
    ['label' => 'See benefits', 'url' => route('benefits.index'), 'style' => 'secondary'],
  ],
])

<div class="max-w-7xl mx-auto px-4 py-12 pb-16">
  <div class="grid lg:grid-cols-3 gap-5 mb-10">
    @foreach([
      ['Career clinic', 'CV and LinkedIn reviews, mock interviews, salary negotiation and portfolio feedback.', 'Monthly'],
      ['Office hours', 'An open room with staff, hub leads and senior members. Bring one question.', 'Monthly'],
      ['Mentorship', 'A structured three-month match with a mentor or mentee, with clear goals.', 'Quarterly cycles'],
    ] as [$title, $copy, $cadence])
      <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <span class="text-[11px] font-bold uppercase tracking-wider text-[#C23A1F]">{{ $cadence }}</span>
        <h2 class="mt-2 font-display text-xl font-bold text-[#101E33]">{{ $title }}</h2>
        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $copy }}</p>
      </article>
    @endforeach
  </div>

  <div class="flex items-end justify-between gap-4 mb-5">
    <h2 class="font-display text-2xl font-bold text-[#101E33]">Upcoming sessions</h2>
    <a href="{{ route('events') }}" class="text-sm font-semibold text-[#C23A1F] hover:underline">All events</a>
  </div>

  @if($events->isEmpty())
    <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">
      New clinic and office-hour dates are announced in the newsletter and on the events page.
    </div>
  @else
    <div class="grid md:grid-cols-2 gap-4">
      @foreach($events as $event)
        <a href="{{ route('events.show', $event->slug) }}" class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-[#FF5C3D]/40">
          <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-lg bg-slate-50 text-[#101E33] leading-none">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#C23A1F]">{{ $event->start_date->format('M') }}</span>
            <span class="font-display text-xl font-extrabold">{{ $event->start_date->format('j') }}</span>
          </div>
          <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $event->category?->name }}</p>
            <p class="truncate font-semibold text-[#101E33]">{{ $event->title }}</p>
            <p class="text-xs text-slate-500">{{ $event->is_online ? 'Online' : ($event->city ?: 'Venue to be announced') }}</p>
          </div>
        </a>
      @endforeach
    </div>
  @endif
</div>
@endsection