@extends('layouts.admin')
@section('content')
@php
  $input = 'w-full rounded-lg border border-slate-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $label = 'block text-sm font-medium text-slate-700 mb-1';
@endphp
<div class="max-w-3xl mx-auto px-4 py-8 pb-12">
  <a href="{{ route('admin.certificates.index') }}" class="text-sm text-[#C23A1F] hover:underline">Back to certificates</a>
  <h1 class="mt-2 font-display text-2xl font-extrabold text-[#101E33]">Issue certificate</h1>

  <form method="POST" action="{{ route('admin.certificates.store') }}" class="mt-6 space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf
    @if($errors->any())
      <div class="rounded-lg border border-red-100 bg-red-50 p-4 text-sm text-red-700">
        <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
      </div>
    @endif

    <div>
      <label for="user_id" class="{{ $label }}">Member</label>
      <select id="user_id" name="user_id" required class="{{ $input }}">
        <option value="">Choose a member</option>
        @foreach($members as $member)
          <option value="{{ $member->id }}" @selected(old('user_id') == $member->id)>{{ $member->name }} — {{ $member->email }}</option>
        @endforeach
      </select>
    </div>
    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label for="type" class="{{ $label }}">Type</label>
        <select id="type" name="type" required class="{{ $input }}">
          @foreach(\App\Models\Certificate::TYPES as $value => $typeLabel)
            <option value="{{ $value }}" @selected(old('type', $certificate->type) === $value)>{{ $typeLabel }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label for="issued_at" class="{{ $label }}">Issued on</label>
        <input id="issued_at" type="date" name="issued_at" value="{{ old('issued_at', $certificate->issued_at?->toDateString() ?? now()->toDateString()) }}" class="{{ $input }}">
      </div>
    </div>
    <div>
      <label for="event_id" class="{{ $label }}">Related event (optional)</label>
      <select id="event_id" name="event_id" class="{{ $input }}">
        <option value="">No event</option>
        @foreach($events as $event)
          <option value="{{ $event->id }}" @selected(old('event_id') == $event->id)>{{ $event->title }} — {{ $event->start_date?->format('M j, Y') }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label for="title" class="{{ $label }}">Certificate title</label>
      <input id="title" name="title" value="{{ old('title', $certificate->title) }}" required class="{{ $input }}" placeholder="e.g. Speaker at Lagos Frontend Meetup">
    </div>
    <div>
      <label for="description" class="{{ $label }}">Description</label>
      <textarea id="description" name="description" rows="4" class="{{ $input }}" placeholder="A short line describing the contribution.">{{ old('description', $certificate->description) }}</textarea>
    </div>

    <div class="flex items-center gap-3">
      <button class="rounded-lg bg-[#FF5C3D] px-5 py-2.5 font-semibold text-white hover:bg-[#e94f32]">Issue certificate</button>
      <a href="{{ route('admin.certificates.index') }}" class="px-4 py-2.5 text-slate-500 hover:text-slate-700">Cancel</a>
    </div>
  </form>
</div>
@endsection