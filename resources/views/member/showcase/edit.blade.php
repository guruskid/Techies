@extends('layouts.app')
@section('title', 'Submit a project · Meet with the Techies')
@section('content')
@include('partials.member-nav')
@php
  $editing = $project->exists;
  $input = 'w-full rounded-lg border border-slate-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $label = 'block text-sm font-medium text-slate-700 mb-1';
  $stackText = old('stack_text', $project->stack ? implode(', ', $project->stack) : '');
@endphp
<div class="max-w-3xl mx-auto px-4 py-8 pb-14">
  <a href="{{ route('projects.index') }}" class="text-sm text-[#C23A1F] hover:underline">Back to showcase</a>
  <h1 class="mt-2 font-display text-2xl font-extrabold text-[#101E33]">{{ $editing ? 'Edit project' : 'Submit a project' }}</h1>
  <p class="mt-1 text-sm text-slate-500">Projects are reviewed before they appear in the public showcase.</p>

  <form method="POST" action="{{ $editing ? route('projects.update', $project) : route('projects.store') }}" class="mt-6 space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf
    @if($editing) @method('PATCH') @endif
    @if($errors->any())<div class="rounded-lg bg-red-50 p-4 text-sm text-red-700"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div><label for="title" class="{{ $label }}">Project title</label><input id="title" name="title" value="{{ old('title', $project->title) }}" required class="{{ $input }}"></div>
    <div><label for="summary" class="{{ $label }}">Short summary</label><textarea id="summary" name="summary" rows="2" required class="{{ $input }}" placeholder="One or two lines about what it does">{{ old('summary', $project->summary) }}</textarea></div>
    <div><label for="description" class="{{ $label }}">Description</label><textarea id="description" name="description" rows="5" class="{{ $input }}" placeholder="What you built, why, and what you learned">{{ old('description', $project->description) }}</textarea></div>
    <div class="grid sm:grid-cols-2 gap-4">
      <div><label for="url" class="{{ $label }}">Live link</label><input id="url" name="url" type="url" value="{{ old('url', $project->url) }}" class="{{ $input }}"></div>
      <div><label for="repo_url" class="{{ $label }}">Repository</label><input id="repo_url" name="repo_url" type="url" value="{{ old('repo_url', $project->repo_url) }}" class="{{ $input }}"></div>
    </div>
    <div><label for="image" class="{{ $label }}">Cover image URL</label><input id="image" name="image" type="url" value="{{ old('image', $project->image) }}" class="{{ $input }}"></div>
    <div><label for="stack_text" class="{{ $label }}">Stack</label><input id="stack_text" name="stack_text" value="{{ $stackText }}" class="{{ $input }}" placeholder="Laravel, Tailwind, MySQL"></div>
    <label class="flex items-center gap-3 text-sm text-slate-700"><input type="checkbox" name="looking_for_collaborators" value="1" @checked(old('looking_for_collaborators', $project->looking_for_collaborators)) class="h-4 w-4 rounded border-slate-300 text-[#FF5C3D]"> Looking for collaborators</label>
    <div class="flex items-center gap-3"><button class="rounded-lg bg-[#FF5C3D] px-5 py-2.5 font-semibold text-white hover:bg-[#e94f32]">{{ $editing ? 'Save changes' : 'Submit project' }}</button><a href="{{ route('projects.index') }}" class="px-4 py-2.5 text-slate-500">Cancel</a></div>
  </form>
</div>
@endsection