@extends('layouts.app')
@section('title', $grant->title.' · Grants')
@section('content')
@include('partials.member-nav')
@php
  $input = 'w-full rounded-lg border border-slate-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $label = 'block text-sm font-medium text-slate-700 mb-1';
@endphp
<div class="max-w-5xl mx-auto px-4 py-8 pb-14">
  <a href="{{ route('grants.index') }}" class="text-sm font-medium text-[#C23A1F] hover:underline">All grants</a>
  <div class="mt-4 grid md:grid-cols-[1.2fr_0.8fr] gap-6 items-start">
    <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <h1 class="font-display text-3xl font-extrabold text-[#101E33]">{{ $grant->title }}</h1>
      <p class="mt-2 text-sm text-slate-500">{{ $grant->amount ? '₦'.number_format($grant->amount) : 'Amount to be confirmed' }} · {{ $grant->closes_at?->format('M j, Y') ?? 'No deadline' }}</p>
      <div class="prose-tech mt-6 max-w-none">{!! \App\Support\RichText::render($grant->description) !!}</div>
    </article>
    <aside class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      @if($application)
        <h2 class="font-display text-lg font-bold text-[#101E33]">Your application</h2>
        <span class="mt-2 inline-block rounded-full px-2 py-1 text-xs {{ $application->status === 'approved' ? 'bg-green-50 text-green-700' : ($application->status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">{{ ucfirst($application->status) }}</span>
        <p class="mt-3 font-medium text-[#101E33]">{{ $application->project_title }}</p>
        @if($application->review_notes)<p class="mt-2 text-sm text-slate-600">{{ $application->review_notes }}</p>@endif
      @elseif($grant->isOpen())
        <h2 class="font-display text-lg font-bold text-[#101E33]">Apply</h2>
        <form method="POST" action="{{ route('grants.apply', $grant->slug) }}" class="mt-3 space-y-3">
          @csrf
          <div><label for="project_title" class="{{ $label }}">Project title</label><input id="project_title" name="project_title" required class="{{ $input }}"></div>
          <div><label for="summary" class="{{ $label }}">Summary</label><textarea id="summary" name="summary" rows="4" required class="{{ $input }}"></textarea></div>
          <div><label for="budget_breakdown" class="{{ $label }}">Budget breakdown</label><textarea id="budget_breakdown" name="budget_breakdown" rows="3" class="{{ $input }}"></textarea></div>
          <button class="w-full rounded-lg bg-[#FF5C3D] px-4 py-2.5 text-sm font-semibold text-white">Submit application</button>
        </form>
      @else
        <p class="text-sm text-slate-500">This grant is closed.</p>
      @endif
    </aside>
  </div>
</div>
@endsection