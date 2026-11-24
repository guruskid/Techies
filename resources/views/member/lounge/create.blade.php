@extends('layouts.app')
@section('title', 'New lounge post · Meet with the Techies')
@section('content')
@include('partials.member-nav')
@php
  $input = 'w-full rounded-lg border border-slate-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $label = 'block text-sm font-medium text-slate-700 mb-1';
@endphp
<div class="max-w-3xl mx-auto px-4 py-8 pb-14">
  <a href="{{ route('lounge.index') }}" class="text-sm font-medium text-[#C23A1F] hover:underline">Back to lounge</a>
  <h1 class="mt-2 font-display text-2xl font-extrabold text-[#101E33]">Start a conversation</h1>
  <form method="POST" action="{{ route('lounge.store') }}" class="mt-6 space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf
    <div><label for="title" class="{{ $label }}">Title</label><input id="title" name="title" value="{{ old('title') }}" required class="{{ $input }}"></div>
    <div><label for="category" class="{{ $label }}">Category</label><select id="category" name="category" class="{{ $input }}">@foreach(['General', 'Jobs', 'Feedback', 'Wins', 'Events', 'Collaboration'] as $category)<option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>@endforeach</select></div>
    <div><label for="body" class="{{ $label }}">Post</label><textarea id="body" name="body" rows="7" required class="{{ $input }}">{{ old('body') }}</textarea></div>
    <button class="rounded-lg bg-[#FF5C3D] px-5 py-2.5 font-semibold text-white hover:bg-[#e94f32]">Publish post</button>
  </form>
</div>
@endsection