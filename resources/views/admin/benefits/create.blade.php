@extends('layouts.admin')
@section('content')
@php
  $editing = $benefit->exists;
  $input = 'w-full rounded-lg border border-slate-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $label = 'block text-sm font-medium text-slate-700 mb-1';
@endphp
<div class="max-w-3xl mx-auto px-4 py-8 pb-12">
  <a href="{{ route('admin.benefits.index') }}" class="text-sm text-[#C23A1F] hover:underline">Back to benefits</a>
  <h1 class="mt-2 font-display text-2xl font-extrabold text-[#101E33]">{{ $editing ? 'Edit benefit' : 'New benefit' }}</h1>

  <form method="POST" action="{{ $editing ? route('admin.benefits.update', $benefit) : route('admin.benefits.store') }}" class="mt-6 space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf
    @if($editing) @method('PATCH') @endif

    @if($errors->any())
      <div class="rounded-lg border border-red-100 bg-red-50 p-4 text-sm text-red-700">
        <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
      </div>
    @endif

    <div>
      <label for="title" class="{{ $label }}">Title</label>
      <input id="title" name="title" value="{{ old('title', $benefit->title) }}" required class="{{ $input }}">
    </div>
    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label for="provider" class="{{ $label }}">Provider</label>
        <input id="provider" name="provider" value="{{ old('provider', $benefit->provider) }}" class="{{ $input }}">
      </div>
      <div>
        <label for="category" class="{{ $label }}">Category</label>
        <input id="category" name="category" value="{{ old('category', $benefit->category ?: 'Partner offer') }}" required class="{{ $input }}">
      </div>
    </div>
    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label for="discount_label" class="{{ $label }}">Offer label</label>
        <input id="discount_label" name="discount_label" value="{{ old('discount_label', $benefit->discount_label) }}" class="{{ $input }}" placeholder="e.g. 20% off">
      </div>
      <div>
        <label for="url" class="{{ $label }}">Link</label>
        <input id="url" name="url" type="url" value="{{ old('url', $benefit->url) }}" class="{{ $input }}" placeholder="https://...">
      </div>
    </div>
    <div>
      <label for="redemption_code" class="{{ $label }}">Redemption code</label>
      <input id="redemption_code" name="redemption_code" value="{{ old('redemption_code', $benefit->redemption_code) }}" class="{{ $input }}" placeholder="Optional code members reveal when they redeem">
    </div>
    <div>
      <label for="redemption_instructions" class="{{ $label }}">Redemption instructions</label>
      <textarea id="redemption_instructions" name="redemption_instructions" rows="3" class="{{ $input }}">{{ old('redemption_instructions', $benefit->redemption_instructions) }}</textarea>
    </div>
    <div>
      <label for="description" class="{{ $label }}">Description</label>
      <textarea id="description" name="description" rows="4" class="{{ $input }}">{{ old('description', $benefit->description) }}</textarea>
    </div>
    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label for="sort_order" class="{{ $label }}">Sort order</label>
        <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $benefit->sort_order ?? 0) }}" class="{{ $input }}">
      </div>
      <label class="flex items-center gap-3 pt-6 text-sm text-slate-700">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $benefit->is_active)) class="h-4 w-4 rounded border-slate-300 text-[#FF5C3D]">
        Show this benefit on the public page
      </label>
    </div>

    <div class="flex items-center gap-3">
      <button class="rounded-lg bg-[#FF5C3D] px-5 py-2.5 font-semibold text-white hover:bg-[#e94f32]">{{ $editing ? 'Save changes' : 'Create benefit' }}</button>
      <a href="{{ route('admin.benefits.index') }}" class="px-4 py-2.5 text-slate-500 hover:text-slate-700">Cancel</a>
    </div>
  </form>
</div>
@endsection