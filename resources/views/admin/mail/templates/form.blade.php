@extends('layouts.admin')
@section('content')
@php
  $editing = $template->exists;
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $input = 'w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $label = 'block text-sm font-medium text-slate-700 mb-1';
@endphp

<div class="max-w-4xl mx-auto px-4 py-8 pb-12">
  <div class="mb-2">
    <a href="{{ route('admin.mail.templates.index') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }}">Back to templates</a>
    <h1 class="mt-2 text-2xl font-bold text-[#101E33]">{{ $editing ? 'Edit template' : 'New email template' }}</h1>
  </div>

  @include('admin.mail._nav')

  @if($errors->any())
    <div class="mb-5 rounded-xl bg-red-50 border border-red-100 p-4 text-sm text-red-700">
      <ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ $editing ? route('admin.mail.templates.update', $template) : route('admin.mail.templates.store') }}"
        class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-5">
    @csrf
    @if($editing) @method('PATCH') @endif

    <div>
      <label for="name" class="{{ $label }}">Template name</label>
      <input id="name" name="name" required maxlength="120" value="{{ old('name', $template->name) }}" placeholder="Chapter welcome" class="{{ $input }}">
    </div>

    <div>
      <label for="subject" class="{{ $label }}">Subject</label>
      <input id="subject" name="subject" required maxlength="255" value="{{ old('subject', $template->subject) }}" placeholder="Welcome to @{{site_name}}" class="{{ $input }}">
    </div>

    <div>
      <label for="body" class="{{ $label }}">Body</label>
      <textarea id="body" name="body" rows="16" required maxlength="50000" class="{{ $input }} font-mono text-[13px]">{{ old('body', $template->body) }}</textarea>
      <p class="mt-2 text-xs text-slate-500">
        HTML is supported. Merge tags: <code class="px-1 bg-slate-100 rounded">@{{name}}</code>,
        <code class="px-1 bg-slate-100 rounded">@{{first_name}}</code>,
        <code class="px-1 bg-slate-100 rounded">@{{email}}</code>,
        <code class="px-1 bg-slate-100 rounded">@{{site_name}}</code>.
        Broadcasts replace them per recipient; direct messages send them as written.
      </p>
    </div>

    <div class="flex flex-wrap gap-3 pt-1">
      <button type="submit" class="px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition {{ $focus }}">{{ $editing ? 'Save template' : 'Create template' }}</button>
      <a href="{{ route('admin.mail.templates.index') }}" class="px-5 py-2.5 border border-slate-200 rounded-lg font-medium hover:bg-slate-50 transition {{ $focus }}">Cancel</a>
    </div>
  </form>
</div>
@endsection
