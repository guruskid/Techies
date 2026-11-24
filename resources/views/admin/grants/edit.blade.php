@extends('layouts.admin')
@section('content')
@php
  $editing = $grant->exists;
  $input = 'w-full rounded-lg border border-slate-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $label = 'block text-sm font-medium text-slate-700 mb-1';
@endphp
<div class="max-w-3xl mx-auto px-4 py-8 pb-12">
  <a href="{{ route('admin.grants.index') }}" class="text-sm text-[#C23A1F] hover:underline">Back to grants</a>
  <h1 class="mt-2 font-display text-2xl font-extrabold text-[#101E33]">{{ $editing ? 'Edit grant' : 'New grant' }}</h1>
  <form method="POST" action="{{ $editing ? route('admin.grants.update', $grant) : route('admin.grants.store') }}" class="mt-6 space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf @if($editing) @method('PATCH') @endif
    <div><label for="title" class="{{ $label }}">Title</label><input id="title" name="title" value="{{ old('title', $grant->title) }}" required class="{{ $input }}"></div>
    <div><label for="description" class="{{ $label }}">Description</label><textarea id="description" name="description" rows="6" data-wysiwyg class="{{ $input }}">{{ old('description', $grant->description) }}</textarea></div>
    <div class="grid sm:grid-cols-2 gap-4">
      <div><label for="amount" class="{{ $label }}">Amount</label><input id="amount" type="number" name="amount" value="{{ old('amount', $grant->amount) }}" class="{{ $input }}"></div>
      <div><label for="status" class="{{ $label }}">Status</label><select id="status" name="status" class="{{ $input }}">@foreach(['draft' => 'Draft', 'open' => 'Open', 'closed' => 'Closed'] as $value => $text)<option value="{{ $value }}" @selected(old('status', $grant->status ?: 'draft') === $value)>{{ $text }}</option>@endforeach</select></div>
    </div>
    <div class="grid sm:grid-cols-2 gap-4">
      <div><label for="opens_at" class="{{ $label }}">Opens</label><input id="opens_at" type="date" name="opens_at" value="{{ old('opens_at', $grant->opens_at?->toDateString()) }}" class="{{ $input }}"></div>
      <div><label for="closes_at" class="{{ $label }}">Closes</label><input id="closes_at" type="date" name="closes_at" value="{{ old('closes_at', $grant->closes_at?->toDateString()) }}" class="{{ $input }}"></div>
    </div>
    <div class="flex gap-3"><button class="rounded-lg bg-[#FF5C3D] px-5 py-2.5 font-semibold text-white">{{ $editing ? 'Save' : 'Create' }}</button><a href="{{ route('admin.grants.index') }}" class="px-4 py-2.5 text-slate-500">Cancel</a></div>
  </form>
</div>
@include('partials.wysiwyg')
@endsection