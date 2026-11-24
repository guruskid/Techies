@extends('layouts.admin')
@section('content')
@php
  $editing = $spotlight->exists;
  $input = 'w-full rounded-lg border border-slate-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $label = 'block text-sm font-medium text-slate-700 mb-1';
@endphp
<div class="max-w-2xl mx-auto px-4 py-8 pb-12">
  <a href="{{ route('admin.spotlights.index') }}" class="text-sm text-[#C23A1F] hover:underline">Back to spotlights</a>
  <h1 class="mt-2 font-display text-2xl font-extrabold text-[#101E33]">{{ $editing ? 'Edit spotlight' : 'New spotlight' }}</h1>
  <form method="POST" action="{{ $editing ? route('admin.spotlights.update', $spotlight) : route('admin.spotlights.store') }}" class="mt-6 space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf @if($editing) @method('PATCH') @endif
    <div><label for="user_id" class="{{ $label }}">Member</label><select id="user_id" name="user_id" required class="{{ $input }}">@foreach($members as $member)<option value="{{ $member->id }}" @selected(old('user_id', $spotlight->user_id) == $member->id)>{{ $member->name }}</option>@endforeach</select></div>
    <div><label for="month" class="{{ $label }}">Month</label><input id="month" type="month" name="month" value="{{ old('month', $spotlight->month ?? now()->format('Y-m')) }}" required class="{{ $input }}"></div>
    <div><label for="quote" class="{{ $label }}">Quote</label><textarea id="quote" name="quote" rows="4" required class="{{ $input }}">{{ old('quote', $spotlight->quote) }}</textarea></div>
    <div><label for="achievement" class="{{ $label }}">Achievement</label><textarea id="achievement" name="achievement" rows="3" class="{{ $input }}">{{ old('achievement', $spotlight->achievement) }}</textarea></div>
    <label class="flex items-center gap-3 text-sm text-slate-700"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $spotlight->is_published ?? true)) class="h-4 w-4 rounded border-slate-300 text-[#FF5C3D]"> Publish on the homepage</label>
    <button class="rounded-lg bg-[#FF5C3D] px-5 py-2.5 font-semibold text-white">{{ $editing ? 'Save changes' : 'Publish spotlight' }}</button>
  </form>
</div>
@endsection