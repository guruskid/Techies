@extends('layouts.app')
@section('content')
@php
  $statusBadge = [
    'pending' => 'bg-amber-50 text-amber-700',
    'approved' => 'bg-green-50 text-green-700',
    'rejected' => 'bg-red-50 text-red-700',
  ];
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-1';
@endphp

<div class="max-w-5xl mx-auto px-4 py-8 pb-12">
  <a href="{{ route('admin.speaker-calls.index') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }} rounded">Back to calls for speakers</a>

  <div class="mt-3 mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold text-[#101E33]">{{ $speakerCall->title }}</h1>
      <p class="mt-1 text-sm text-slate-500">Review proposals and email each speaker the decision.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('admin.speaker-calls.edit', $speakerCall) }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 transition {{ $focus }}">Edit call</a>
      @if($speakerCall->status !== 'draft')
        <a href="{{ route('speaker-calls.show', $speakerCall->slug) }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 transition {{ $focus }}">View public page</a>
      @endif
    </div>
  </div>

  @if($speakerCall->topicList() !== [])
    <div class="mb-6 flex flex-wrap gap-2">
      @foreach($speakerCall->topicList() as $topic)
        <span class="px-3 py-1 rounded-full text-xs bg-[#101E33]/5 text-[#101E33]">{{ $topic }}</span>
      @endforeach
    </div>
  @endif

  <div class="space-y-5">
    @forelse($submissions as $submission)
      <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6" aria-labelledby="submission-{{ $submission->id }}">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
          <div>
            <h2 id="submission-{{ $submission->id }}" class="text-lg font-bold text-[#101E33]">{{ $submission->title }}</h2>
            <p class="mt-1 text-sm text-slate-600">
              {{ $submission->user?->name ?? 'Deleted member' }}
              @if($submission->user?->email) · <span class="break-all">{{ $submission->user->email }}</span>@endif
            </p>
          </div>
          <span class="shrink-0 px-2.5 py-1 rounded-full text-xs font-medium {{ $statusBadge[$submission->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($submission->status) }}</span>
        </div>

        <dl class="mt-4 grid sm:grid-cols-2 gap-3 text-sm">
          <div>
            <dt class="text-slate-500">Chosen topic</dt>
            <dd class="font-medium text-[#101E33]">{{ $submission->topic }}</dd>
          </div>
          <div>
            <dt class="text-slate-500">Submitted</dt>
            <dd class="font-medium text-[#101E33]">{{ $submission->created_at?->format('M j, Y') }}</dd>
          </div>
        </dl>

        <div class="mt-4">
          <h3 class="text-sm font-semibold text-[#101E33]">Abstract</h3>
          <p class="mt-1 text-sm text-slate-700 whitespace-pre-line">{{ $submission->abstract }}</p>
        </div>

        @if($submission->bio)
          <div class="mt-4">
            <h3 class="text-sm font-semibold text-[#101E33]">Speaker bio</h3>
            <p class="mt-1 text-sm text-slate-700 whitespace-pre-line">{{ $submission->bio }}</p>
          </div>
        @endif

        @if($submission->review_notes)
          <div class="mt-4 p-3 rounded-lg bg-slate-50 border border-slate-100">
            <h3 class="text-sm font-semibold text-[#101E33]">Previous review notes</h3>
            <p class="mt-1 text-sm text-slate-700 whitespace-pre-line">{{ $submission->review_notes }}</p>
            @if($submission->reviewer)
              <p class="mt-1 text-xs text-slate-500">By {{ $submission->reviewer->name }} on {{ $submission->reviewed_at?->format('M j, Y') }}</p>
            @endif
          </div>
        @endif

        <form method="POST" action="{{ route('admin.speaker-calls.submissions.review', [$speakerCall, $submission]) }}" class="mt-5 border-t border-slate-100 pt-5 space-y-3">
          @csrf
          <div class="grid sm:grid-cols-[minmax(0,1fr)_minmax(0,2fr)_auto] gap-3 sm:items-end">
            <div>
              <label for="status-{{ $submission->id }}" class="block text-sm font-medium text-slate-700 mb-1">Decision</label>
              <select id="status-{{ $submission->id }}" name="status" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                <option value="approved" @selected($submission->status === 'approved')>Approve</option>
                <option value="rejected" @selected($submission->status === 'rejected')>Reject</option>
              </select>
            </div>
            <div>
              <label for="notes-{{ $submission->id }}" class="block text-sm font-medium text-slate-700 mb-1">Notes to the speaker (optional)</label>
              <input id="notes-{{ $submission->id }}" name="review_notes" value="{{ old('review_notes', $submission->review_notes) }}" maxlength="2000" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="Shared in the decision email">
            </div>
            <button class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition {{ $focus }}">Save decision</button>
          </div>
          <p class="text-xs text-slate-500">Saving a decision emails the speaker. Re-saving the same decision does not send another email.</p>
        </form>
      </section>
    @empty
      <div class="bg-white rounded-2xl border border-slate-100 p-10 text-center shadow-sm">
        <h2 class="text-lg font-bold text-[#101E33]">No submissions yet</h2>
        <p class="mt-2 text-sm text-slate-500">Share the public call link so members can submit a topic.</p>
        @if($speakerCall->status !== 'draft')
          <a href="{{ route('speaker-calls.show', $speakerCall->slug) }}" class="mt-4 inline-block px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Open public page</a>
        @endif
      </div>
    @endforelse
  </div>
</div>
@endsection