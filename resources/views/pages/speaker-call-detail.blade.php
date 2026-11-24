@extends('layouts.app')
@section('title', $speakerCall->title.' · Call for Speakers')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags((string) $speakerCall->description), 160) ?: 'Submit a talk for '.$speakerCall->title.'.')
@section('canonical', route('speaker-calls.show', $speakerCall->slug))

@section('content')
@php
  $isOpen = $speakerCall->isOpen();
  $source = $speakerCall->event?->title ?? $speakerCall->hub?->name ?? $speakerCall->chapter?->name ?? 'Meet with the Techies';
  $submissionBadge = [
    'pending' => 'bg-amber-50 text-amber-700',
    'approved' => 'bg-green-50 text-green-700',
    'rejected' => 'bg-red-50 text-red-700',
  ];
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
  $inputClass = 'w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $labelClass = 'block text-sm font-medium text-slate-700 mb-1';
@endphp

<section class="bg-[#101E33] text-white py-12 md:py-16">
  <div class="max-w-5xl mx-auto px-4">
    <a href="{{ route('speaker-calls.index') }}" class="text-sm text-slate-300 hover:text-white hover:underline rounded {{ $focus }}">All calls for speakers</a>
    <p class="mt-4 text-xs font-medium text-[#FF5C3D]">{{ $source }}</p>
    <h1 class="mt-1 text-3xl md:text-4xl font-bold leading-tight">{{ $speakerCall->title }}</h1>
    <div class="mt-4 flex flex-wrap items-center gap-3 text-sm text-slate-300">
      <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $isOpen ? 'bg-green-500/20 text-green-200' : 'bg-white/10 text-slate-200' }}">{{ $isOpen ? 'Open for submissions' : 'Closed' }}</span>
      @if($speakerCall->closes_at)
        <span>Closes {{ $speakerCall->closes_at->format('M j, Y') }}</span>
      @endif
      @if($speakerCall->event)
        <a href="{{ route('events.show', $speakerCall->event->slug) }}" class="hover:text-white hover:underline">{{ $speakerCall->event->title }}</a>
      @endif
    </div>
  </div>
</section>

<div class="max-w-5xl mx-auto px-4 py-8 pb-12 grid lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] gap-8 items-start">
  <div class="space-y-6">
    @if($speakerCall->description)
      <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 article-body">
        {!! \App\Support\RichText::render($speakerCall->description) !!}
      </section>
    @endif

    <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
      <h2 class="text-lg font-bold text-[#101E33]">Topics</h2>
      <p class="mt-1 text-sm text-slate-500">Choose one of these topics when you submit your proposal.</p>
      <ul class="mt-4 flex flex-wrap gap-2">
        @foreach($speakerCall->topicList() as $topic)
          <li class="px-3 py-1.5 rounded-full text-sm bg-[#101E33]/5 text-[#101E33]">{{ $topic }}</li>
        @endforeach
      </ul>
    </section>

    @if($speakerCall->submissions->isNotEmpty())
      <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-lg font-bold text-[#101E33]">Confirmed speakers</h2>
        <ul class="mt-4 divide-y divide-slate-100">
          @foreach($speakerCall->submissions as $approved)
            <li class="py-3">
              <p class="font-medium text-[#101E33]">{{ $approved->user?->name ?? 'Community member' }}</p>
              <p class="text-sm text-slate-600">{{ $approved->title }}</p>
              <p class="text-xs text-slate-500 mt-0.5">{{ $approved->topic }}</p>
            </li>
          @endforeach
        </ul>
      </section>
    @endif
  </div>

  <aside class="space-y-6">
    @if($submission)
      <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-lg font-bold text-[#101E33]">Your proposal</h2>
        <span class="mt-2 inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ $submissionBadge[$submission->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($submission->status) }}</span>
        <p class="mt-3 font-medium text-[#101E33]">{{ $submission->title }}</p>
        <p class="text-sm text-slate-600">{{ $submission->topic }}</p>
        @if($submission->review_notes)
          <div class="mt-4 p-3 rounded-lg bg-slate-50 border border-slate-100 text-sm text-slate-700 whitespace-pre-line">{{ $submission->review_notes }}</div>
        @endif
        <p class="mt-4 text-xs text-slate-500">The organiser will email you when the decision is ready.</p>
      </section>
    @elseif($isOpen)
      <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-lg font-bold text-[#101E33]">Submit a talk</h2>
        <p class="mt-1 text-sm text-slate-500">One proposal per member for this call.</p>

        @if(! auth()->check())
          <p class="mt-4 text-sm text-slate-600">Sign in to submit a topic. New here? Create a free account first.</p>
          <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('login') }}" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition {{ $focus }}">Sign in</a>
            <a href="{{ route('register') }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 transition {{ $focus }}">Create account</a>
          </div>
        @elseif(! auth()->user()->hasVerifiedEmail())
          <p class="mt-4 text-sm text-slate-600">Verify your email address before submitting a proposal.</p>
          <a href="{{ route('verification.notice') }}" class="mt-4 inline-block px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition">Verify email</a>
        @else
          <form method="POST" action="{{ route('speaker-calls.submit', $speakerCall->slug) }}" class="mt-4 space-y-4">
            @csrf
            <div>
              <label for="topic" class="{{ $labelClass }}">Topic</label>
              <select id="topic" name="topic" required class="{{ $inputClass }}">
                <option value="">Choose a topic</option>
                @foreach($speakerCall->topicList() as $topic)
                  <option value="{{ $topic }}" @selected(old('topic') === $topic)>{{ $topic }}</option>
                @endforeach
              </select>
              @error('topic')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
              <label for="title" class="{{ $labelClass }}">Talk title</label>
              <input id="title" name="title" value="{{ old('title') }}" required maxlength="160" class="{{ $inputClass }}" placeholder="A clear, specific title">
              @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
              <label for="abstract" class="{{ $labelClass }}">Abstract</label>
              <textarea id="abstract" name="abstract" rows="5" required maxlength="2000" class="{{ $inputClass }}" placeholder="What will you cover, and what will the audience take away?">{{ old('abstract') }}</textarea>
              @error('abstract')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
              <label for="bio" class="{{ $labelClass }}">Short bio (optional)</label>
              <textarea id="bio" name="bio" rows="3" maxlength="1000" class="{{ $inputClass }}" placeholder="A sentence or two about you and your work.">{{ old('bio') }}</textarea>
              @error('bio')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <button class="w-full px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition {{ $focus }}">Submit proposal</button>
          </form>
        @endif
      </section>
    @else
      <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-lg font-bold text-[#101E33]">Submissions are closed</h2>
        <p class="mt-2 text-sm text-slate-600">This call is no longer accepting proposals. Follow Meet with the Techies for future opportunities.</p>
        <a href="{{ route('speaker-calls.index') }}" class="mt-4 inline-block px-4 py-2 border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 transition">See open calls</a>
      </section>
    @endif

    <section class="bg-[#101E33] text-slate-200 rounded-2xl p-6">
      <h2 class="font-bold text-white">How it works</h2>
      <ol class="mt-3 space-y-2 text-sm list-decimal list-inside text-slate-300">
        <li>Choose one of the published topics.</li>
        <li>Submit a title, abstract and a short bio.</li>
        <li>The organiser reviews every proposal.</li>
        <li>You receive an email with the decision.</li>
      </ol>
    </section>
  </aside>
</div>
@endsection