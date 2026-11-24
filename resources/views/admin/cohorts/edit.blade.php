@extends('layouts.admin')
@section('content')
@php
  $editing = $cohort->exists;
  $input = 'w-full rounded-lg border border-slate-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $label = 'block text-sm font-medium text-slate-700 mb-1';
@endphp
<div class="max-w-3xl mx-auto px-4 py-8 pb-12">
  <a href="{{ route('admin.cohorts.index') }}" class="text-sm text-[#C23A1F] hover:underline">Back to cohorts</a>
  <h1 class="mt-2 font-display text-2xl font-extrabold text-[#101E33]">{{ $editing ? 'Edit cohort' : 'New cohort' }}</h1>
  <form method="POST" action="{{ $editing ? route('admin.cohorts.update', $cohort) : route('admin.cohorts.store') }}" class="mt-6 space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf
    @if($editing) @method('PATCH') @endif
    @if($errors->any())<div class="rounded-lg bg-red-50 p-4 text-sm text-red-700"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div><label for="title" class="{{ $label }}">Title</label><input id="title" name="title" value="{{ old('title', $cohort->title) }}" required class="{{ $input }}"></div>
    <div><label for="track" class="{{ $label }}">Track</label><input id="track" name="track" value="{{ old('track', $cohort->track) }}" class="{{ $input }}" placeholder="e.g. Frontend, Data, Product"></div>
    <div><label for="description" class="{{ $label }}">Description</label><textarea id="description" name="description" rows="6" data-wysiwyg class="{{ $input }}">{{ old('description', $cohort->description) }}</textarea></div>
    <div class="grid sm:grid-cols-2 gap-4">
      <div><label for="starts_at" class="{{ $label }}">Starts</label><input id="starts_at" type="date" name="starts_at" value="{{ old('starts_at', $cohort->starts_at?->toDateString()) }}" class="{{ $input }}"></div>
      <div><label for="ends_at" class="{{ $label }}">Ends</label><input id="ends_at" type="date" name="ends_at" value="{{ old('ends_at', $cohort->ends_at?->toDateString()) }}" class="{{ $input }}"></div>
    </div>
    <div class="grid sm:grid-cols-2 gap-4">
      <div><label for="capacity" class="{{ $label }}">Capacity</label><input id="capacity" type="number" min="1" name="capacity" value="{{ old('capacity', $cohort->capacity) }}" class="{{ $input }}"></div>
      <div><label for="status" class="{{ $label }}">Status</label><select id="status" name="status" class="{{ $input }}">@foreach(['draft' => 'Draft', 'open' => 'Open', 'closed' => 'Closed'] as $value => $text)<option value="{{ $value }}" @selected(old('status', $cohort->status ?: 'draft') === $value)>{{ $text }}</option>@endforeach</select></div>
    </div>
    <div class="flex items-center gap-3"><button class="rounded-lg bg-[#FF5C3D] px-5 py-2.5 font-semibold text-white hover:bg-[#e94f32]">{{ $editing ? 'Save changes' : 'Create cohort' }}</button><a href="{{ route('admin.cohorts.index') }}" class="px-4 py-2.5 text-slate-500">Cancel</a></div>
  </form>
</div>
@include('partials.wysiwyg')
@endsection