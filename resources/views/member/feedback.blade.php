@extends('layouts.app')
@section('title', 'Membership feedback · Meet with the Techies')
@section('content')
@include('partials.member-nav')
<div class="max-w-2xl mx-auto px-4 py-10 pb-16">
  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#C23A1F]">Member feedback</p>
  <h1 class="mt-2 font-display text-3xl font-extrabold text-[#101E33]">How is your membership going?</h1>
  <p class="mt-2 text-slate-600">A short answer helps us improve events, mentorship and opportunities for everyone.</p>

  @if($feedback?->submitted_at)
    <div class="mt-6 rounded-xl border border-green-100 bg-green-50 p-5 text-sm text-green-800">
      <p class="font-semibold">Thank you for your feedback.</p>
      <p class="mt-1">You rated your experience {{ $feedback->rating }} out of 5.</p>
      @if($feedback->comments)<p class="mt-2 whitespace-pre-line">{{ $feedback->comments }}</p>@endif
      <a href="{{ route('feedback.show') }}" class="mt-3 inline-block text-sm font-semibold text-green-900 underline">Update my feedback</a>
    </div>
  @endif

  <form method="POST" action="{{ route('feedback.store') }}" class="mt-6 space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf
    <div>
      <label class="block text-sm font-medium text-slate-700">How would you rate your experience?</label>
      <select name="rating" required class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
        @for($i = 1; $i <= 5; $i++)
          <option value="{{ $i }}" @selected(old('rating', $feedback?->rating) == $i)>{{ $i }} — {{ ['','Not a fit','Needs work','Good','Very good','Excellent'][$i] }}</option>
        @endfor
      </select>
    </div>
    <div>
      <label for="comments" class="block text-sm font-medium text-slate-700">What should we improve?</label>
      <textarea id="comments" name="comments" rows="5" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]" placeholder="Anything that would make your membership more useful">{{ old('comments', $feedback?->comments) }}</textarea>
    </div>
    <button class="rounded-lg bg-[#FF5C3D] px-5 py-2.5 font-semibold text-white hover:bg-[#e94f32]">Submit feedback</button>
  </form>
</div>
@endsection