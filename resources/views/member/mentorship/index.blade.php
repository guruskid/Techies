@extends('layouts.app')
@section('title', 'Mentorship · Meet with the Techies')
@section('content')
@include('partials.member-nav')
@php
  $input = 'w-full rounded-lg border border-slate-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $label = 'block text-sm font-medium text-slate-700 mb-1';
@endphp

<div class="max-w-5xl mx-auto px-4 py-8 pb-14">
  <div class="mb-6">
    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#C23A1F]">Grow with support</p>
    <h1 class="mt-2 font-display text-2xl md:text-3xl font-extrabold text-[#101E33]">Mentorship</h1>
    <p class="mt-1 text-sm text-slate-500">Request a mentor or volunteer to mentor another member. Matches run for three months.</p>
  </div>

  <div class="grid md:grid-cols-[1.2fr_0.8fr] gap-6 items-start">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      @if($request?->isMatched())
        <div class="rounded-xl border border-green-100 bg-green-50 p-5">
          <p class="text-sm font-semibold text-green-800">You are matched with {{ $request->match?->name }}</p>
          <p class="mt-1 text-sm text-green-700">Matched {{ $request->matched_at?->format('M j, Y') }}. Reach out and agree on a first check-in.</p>
        </div>
      @else
        <h2 class="font-display text-xl font-bold text-[#101E33]">{{ $request ? 'Update your mentorship request' : 'Join mentorship' }}</h2>
        <p class="mt-1 text-sm text-slate-500">Tell us what you need or what you can offer. The team matches people carefully.</p>

        <form method="POST" action="{{ route('mentorship.store') }}" class="mt-5 space-y-4">
          @csrf
          <div>
            <label for="role" class="{{ $label }}">I want to</label>
            <select id="role" name="role" class="{{ $input }}" required>
              @foreach(\App\Models\MentorshipRequest::roles() as $value => $roleLabel)
                <option value="{{ $value }}" @selected(old('role', $request?->role) === $value)>{{ $roleLabel }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label for="expertise" class="{{ $label }}">Expertise or focus area</label>
            <input id="expertise" name="expertise" value="{{ old('expertise', $request?->expertise) }}" class="{{ $input }}" placeholder="e.g. frontend, data, product, career growth">
          </div>
          <div>
            <label for="goals" class="{{ $label }}">What do you want to get out of it?</label>
            <textarea id="goals" name="goals" rows="4" class="{{ $input }}" placeholder="A goal you can work on over three months">{{ old('goals', $request?->goals) }}</textarea>
          </div>
          <div>
            <label for="availability" class="{{ $label }}">Availability</label>
            <input id="availability" name="availability" value="{{ old('availability', $request?->availability) }}" class="{{ $input }}" placeholder="e.g. weekday evenings, two hours a month">
          </div>
          @if($request)
            <p class="text-xs text-slate-500">Your current request is {{ $request->status }}. Saving updates it.</p>
          @endif
          <button class="rounded-lg bg-[#FF5C3D] px-5 py-2.5 font-semibold text-white hover:bg-[#e94f32]">{{ $request ? 'Save request' : 'Request a match' }}</button>
        </form>
      @endif
    </div>

    <aside class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <h2 class="font-display text-lg font-bold text-[#101E33]">How it works</h2>
      <ol class="mt-3 space-y-3 text-sm text-slate-600 list-decimal list-inside">
        <li>Tell us whether you want to mentor or be mentored.</li>
        <li>The team matches you with someone aligned to your goals.</li>
        <li>You receive an email with your match and next steps.</li>
        <li>Meet for a three-month cycle and close with a short reflection.</li>
      </ol>
      <p class="mt-4 text-xs text-slate-500">Mentorship is a member benefit. Keep your profile and availability up to date.</p>
    </aside>
  </div>
</div>
@endsection