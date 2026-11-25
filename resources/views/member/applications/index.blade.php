@extends('layouts.app')
@section('title', 'My applications · Meet with the Techies')
@section('content')
@include('partials.member-nav')
@php
  $statusStyle = fn ($status) => in_array($status, ['accepted', 'approved', 'matched', 'shortlisted'], true)
    ? 'bg-green-50 text-green-700'
    : (in_array($status, ['rejected', 'closed'], true) ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-800');
  $statusLabel = fn ($status) => \Illuminate\Support\Str::headline((string) $status);
@endphp
<main class="mx-auto max-w-6xl px-4 py-8">
  <header class="mb-8 rounded-3xl bg-[#101E33] p-6 text-white sm:p-8">
    <p class="text-xs font-bold uppercase tracking-widest text-[#FFB4A4]">Your progress</p>
    <h1 class="mt-2 font-display text-3xl font-extrabold">My applications</h1>
    <p class="mt-2 max-w-2xl text-sm text-slate-300">Track opportunities, grants, speaker submissions, and mentorship from one place.</p>
  </header>

  <div class="space-y-8">
    <section aria-labelledby="opportunity-applications-heading">
      <div class="mb-3 flex items-center justify-between gap-3">
        <h2 id="opportunity-applications-heading" class="font-display text-xl font-bold text-[#101E33]">Opportunities</h2>
        <a href="{{ route('opportunities') }}" class="text-sm font-semibold text-[#C23A1F] hover:underline">Browse opportunities</a>
      </div>
      <div class="space-y-3">
        @forelse($opportunityApplications as $application)
          <article class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
              <p class="text-xs text-slate-500">Applied {{ $application->applied_at?->diffForHumans() ?? $application->created_at->diffForHumans() }}</p>
              @if($application->opportunity)
                <a href="{{ route('opportunities.show', $application->opportunity->slug) }}" class="mt-1 block font-semibold text-[#101E33] hover:text-[#C23A1F]">{{ $application->opportunity->title }}</a>
              @else
                <p class="mt-1 font-semibold text-[#101E33]">Opportunity no longer listed</p>
              @endif
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyle($application->status) }}">{{ $statusLabel($application->status) }}</span>
          </article>
        @empty
          <p class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-600">You haven’t applied to an opportunity yet.</p>
        @endforelse
      </div>
      <div class="mt-3">{{ $opportunityApplications->links() }}</div>
    </section>

    <section aria-labelledby="grant-applications-heading">
      <div class="mb-3 flex items-center justify-between gap-3">
        <h2 id="grant-applications-heading" class="font-display text-xl font-bold text-[#101E33]">Micro-grants</h2>
        <a href="{{ route('grants.index') }}" class="text-sm font-semibold text-[#C23A1F] hover:underline">Browse grants</a>
      </div>
      <div class="space-y-3">
        @forelse($grantApplications as $application)
          <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
              <div>
                <p class="text-xs text-slate-500">Submitted {{ $application->created_at->diffForHumans() }}</p>
                @if($application->grant)<a href="{{ route('grants.show', $application->grant->slug) }}" class="mt-1 block font-semibold text-[#101E33] hover:text-[#C23A1F]">{{ $application->grant->title }}</a>@else<p class="mt-1 font-semibold text-[#101E33]">Grant no longer listed</p>@endif
                <p class="mt-1 text-sm text-slate-600">{{ $application->project_title }}</p>
              </div>
              <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyle($application->status) }}">{{ $statusLabel($application->status) }}</span>
            </div>
            @if($application->review_notes)<p class="mt-4 border-t border-slate-100 pt-3 text-sm text-slate-600"><strong>Review update:</strong> {{ $application->review_notes }}</p>@endif
          </article>
        @empty
          <p class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-600">You haven’t applied for a micro-grant yet.</p>
        @endforelse
      </div>
      <div class="mt-3">{{ $grantApplications->links() }}</div>
    </section>

    <section aria-labelledby="speaker-submissions-heading">
      <div class="mb-3 flex items-center justify-between gap-3">
        <h2 id="speaker-submissions-heading" class="font-display text-xl font-bold text-[#101E33]">Speaker submissions</h2>
        <a href="{{ route('speaker-calls.index') }}" class="text-sm font-semibold text-[#C23A1F] hover:underline">Browse speaker calls</a>
      </div>
      <div class="space-y-3">
        @forelse($speakerSubmissions as $submission)
          <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
              <div>
                <p class="text-xs text-slate-500">Submitted {{ $submission->created_at->diffForHumans() }}</p>
                @if($submission->speakerCall)<a href="{{ route('speaker-calls.show', $submission->speakerCall->slug) }}" class="mt-1 block font-semibold text-[#101E33] hover:text-[#C23A1F]">{{ $submission->speakerCall->title }}</a>@else<p class="mt-1 font-semibold text-[#101E33]">Speaker call no longer listed</p>@endif
                <p class="mt-1 text-sm text-slate-600">{{ $submission->title }}</p>
              </div>
              <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyle($submission->status) }}">{{ $statusLabel($submission->status) }}</span>
            </div>
            @if($submission->review_notes)<p class="mt-4 border-t border-slate-100 pt-3 text-sm text-slate-600"><strong>Review update:</strong> {{ $submission->review_notes }}</p>@endif
          </article>
        @empty
          <p class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-600">You haven’t submitted a talk yet.</p>
        @endforelse
      </div>
      <div class="mt-3">{{ $speakerSubmissions->links() }}</div>
    </section>

    <section aria-labelledby="mentorship-status-heading">
      <h2 id="mentorship-status-heading" class="mb-3 font-display text-xl font-bold text-[#101E33]">Mentorship</h2>
      @if($mentorshipRequest)
        <article class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div>
            <p class="font-semibold text-[#101E33]">{{ $mentorshipRequest->role === 'mentor' ? 'Mentor request' : 'Mentor search' }}</p>
            <p class="mt-1 text-sm text-slate-600">{{ $mentorshipRequest->isMatched() ? 'Matched with '.$mentorshipRequest->match?->name : 'Focus: '.($mentorshipRequest->expertise ?: 'Not specified') }}</p>
          </div>
          <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyle($mentorshipRequest->status) }}">{{ $statusLabel($mentorshipRequest->status) }}</span>
          <a href="{{ route('mentorship.index') }}" class="text-sm font-semibold text-[#C23A1F] hover:underline">Manage request</a>
        </article>
      @else
        <p class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-600">You don’t have an active mentorship request. <a href="{{ route('mentorship.index') }}" class="font-semibold text-[#C23A1F] hover:underline">Explore mentorship</a></p>
      @endif
    </section>
  </div>
</main>
@endsection
