@extends('layouts.app')
@section('title', 'Introductions · Meet with the Techies')
@section('content')
@include('partials.member-nav')
@php
  $input = 'w-full rounded-lg border border-slate-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $label = 'block text-sm font-medium text-slate-700 mb-1';
@endphp
<div class="max-w-5xl mx-auto px-4 py-8 pb-14">
  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#C23A1F]">Warm introductions</p>
  <h1 class="mt-2 font-display text-2xl md:text-3xl font-extrabold text-[#101E33]">Ask for an introduction</h1>
  <p class="mt-1 text-sm text-slate-500">Tell us who you want to meet and why. The team will review the request and make a warm introduction where it makes sense.</p>

  <div class="mt-6 grid md:grid-cols-[1.1fr_0.9fr] gap-6 items-start">
    <form method="POST" action="{{ route('introductions.store') }}" class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      @csrf
      <div>
        <label for="target_user_id" class="{{ $label }}">Member you want to meet</label>
        <select id="target_user_id" name="target_user_id" class="{{ $input }}">
          <option value="">Not sure yet / describe below</option>
          @foreach($members as $member)
            <option value="{{ $member->id }}" @selected(old('target_user_id') == $member->id)>{{ $member->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label for="target_name" class="{{ $label }}">Or describe who you want to meet</label>
        <input id="target_name" name="target_name" value="{{ old('target_name') }}" class="{{ $input }}" placeholder="e.g. a product designer working on fintech">
      </div>
      <div>
        <label for="purpose" class="{{ $label }}">What is the introduction for?</label>
        <input id="purpose" name="purpose" value="{{ old('purpose') }}" required class="{{ $input }}" placeholder="e.g. advice on moving into product design">
      </div>
      <div>
        <label for="context" class="{{ $label }}">Context</label>
        <textarea id="context" name="context" rows="4" class="{{ $input }}" placeholder="A short note you are comfortable sharing.">{{ old('context') }}</textarea>
      </div>
      <button class="rounded-lg bg-[#FF5C3D] px-5 py-2.5 font-semibold text-white hover:bg-[#e94f32]">Request introduction</button>
    </form>

    <aside class="space-y-4">
      <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-display text-lg font-bold text-[#101E33]">Your requests</h2>
        @forelse($requests as $intro)
          <div class="mt-3 rounded-lg border border-slate-100 p-3 text-sm">
            <div class="flex items-center justify-between gap-3">
              <span class="font-medium text-[#101E33]">{{ $intro->target?->name ?? $intro->target_name ?? 'To be matched' }}</span>
              <span class="rounded-full px-2 py-0.5 text-xs {{ $intro->status === 'completed' ? 'bg-green-50 text-green-700' : ($intro->status === 'declined' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">{{ ucfirst($intro->status) }}</span>
            </div>
            <p class="mt-1 text-slate-600">{{ $intro->purpose }}</p>
            @if($intro->admin_notes)<p class="mt-1 text-xs text-slate-500">{{ $intro->admin_notes }}</p>@endif
          </div>
        @empty
          <p class="mt-2 text-sm text-slate-500">No introduction requests yet.</p>
        @endforelse
      </div>
    </aside>
  </div>
</div>
@endsection