@extends('layouts.app')
@section('title', 'Project showcase · Meet with the Techies')
@section('content')
@auth
@include('partials.member-nav')
@endauth
<div class="max-w-7xl mx-auto px-4 py-8 pb-14">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
      <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#C23A1F]">Built by members</p>
      <h1 class="mt-2 font-display text-2xl md:text-3xl font-extrabold text-[#101E33]">Project showcase</h1>
      <p class="mt-1 text-sm text-slate-500">Share what you are building, get feedback and find collaborators.</p>
    </div>
    @auth
      <a href="{{ route('projects.create') }}" class="rounded-lg bg-[#FF5C3D] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#e94f32]">Submit a project</a>
    @else
      <a href="{{ route('register') }}" class="rounded-lg bg-[#FF5C3D] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#e94f32]">Join to submit a project</a>
    @endauth
  </div>

  @if($mine->isNotEmpty())
    <section class="mt-8 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
      <h2 class="font-display text-lg font-bold text-[#101E33]">Your submissions</h2>
      <ul class="mt-3 divide-y divide-slate-100">
        @foreach($mine as $project)
          <li class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm">
            <span class="font-medium text-[#101E33]">{{ $project->title }}</span>
            <span class="rounded-full px-2 py-0.5 text-xs {{ $project->status === 'approved' ? 'bg-green-50 text-green-700' : ($project->status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">{{ ucfirst($project->status) }}</span>
            <a href="{{ route('projects.edit', $project) }}" class="text-[#C23A1F] hover:underline">Edit</a>
          </li>
        @endforeach
      </ul>
    </section>
  @endif

  <div class="mt-8 grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($projects as $project)
      <article class="flex h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        @if($project->image)
          <img src="{{ $project->image }}" alt="" class="h-40 w-full object-cover">
        @else
          <div class="flex h-40 items-center justify-center bg-[#101E33] font-display text-2xl font-bold text-white/80">{{ strtoupper(substr($project->title, 0, 1)) }}</div>
        @endif
        <div class="flex flex-1 flex-col p-5">
          <h2 class="font-display text-lg font-bold text-[#101E33]">{{ $project->title }}</h2>
          <p class="mt-1 text-xs text-slate-500">By {{ $project->user?->name }}</p>
          <p class="mt-2 flex-1 text-sm text-slate-600">{{ $project->summary }}</p>
          @if($project->looking_for_collaborators)
            <span class="mt-3 self-start rounded-full bg-[#FF5C3D]/10 px-2.5 py-1 text-[10px] font-semibold text-[#C23A1F]">Looking for collaborators</span>
          @endif
          <div class="mt-4 flex flex-wrap gap-3 text-sm">
            @if($project->url)<a href="{{ $project->url }}" target="_blank" rel="noopener" class="font-semibold text-[#C23A1F] hover:underline">View project</a>@endif
            @if($project->repo_url)<a href="{{ $project->repo_url }}" target="_blank" rel="noopener" class="font-semibold text-slate-600 hover:underline">Code</a>@endif
          </div>
        </div>
      </article>
    @empty
      <p class="text-slate-500 md:col-span-3">No projects yet. Be the first to submit one.</p>
    @endforelse
  </div>
  <div class="mt-4">{{ $projects->links() }}</div>
</div>
@endsection