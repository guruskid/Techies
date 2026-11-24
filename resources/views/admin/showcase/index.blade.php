@extends('layouts.admin')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <div class="mb-6"><h1 class="font-display text-2xl font-extrabold text-[#101E33]">Project showcase</h1><p class="mt-1 text-sm text-slate-500">Review member projects and feature the strongest work.</p></div>
  <div class="space-y-4">
    @forelse($projects as $project)
      <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <p class="font-medium text-[#101E33]">{{ $project->title }}</p>
            <p class="text-xs text-slate-500">By {{ $project->user?->name }} · {{ $project->created_at?->format('M j, Y') }}</p>
            <p class="mt-2 text-sm text-slate-600">{{ $project->summary }}</p>
          </div>
          <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $project->status === 'approved' ? 'bg-green-50 text-green-700' : ($project->status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">{{ ucfirst($project->status) }}</span>
        </div>
        <form method="POST" action="{{ route('admin.showcase.moderate', $project) }}" class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
          @csrf
          <select name="decision" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option value="approved">Approve</option>
            <option value="rejected">Reject</option>
          </select>
          <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="featured" value="1" @checked($project->is_featured) class="h-4 w-4 rounded border-slate-300 text-[#FF5C3D]"> Feature</label>
          <button class="rounded-lg bg-[#FF5C3D] px-4 py-2 text-sm font-semibold text-white">Save</button>
        </form>
        <div class="mt-2 flex items-center gap-4">
          <a href="{{ route('projects.edit', $project) }}" class="text-sm text-[#C23A1F] hover:underline">Edit</a>
          <form method="POST" action="{{ route('admin.showcase.destroy', $project) }}" onsubmit="return confirm('Delete this project?')">@csrf @method('DELETE')<button class="text-sm text-red-600 hover:underline">Delete</button></form>
        </div>
      </article>
    @empty
      <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">No projects yet.</div>
    @endforelse
  </div>
  <div class="mt-4">{{ $projects->links() }}</div>
</div>
@endsection