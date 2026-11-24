@extends('layouts.admin')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 pb-12">
  <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div><h1 class="font-display text-2xl font-extrabold text-[#101E33]">Member spotlights</h1><p class="mt-1 text-sm text-slate-500">Recognise members and share their work on the homepage.</p></div>
    <a href="{{ route('admin.spotlights.create') }}" class="rounded-lg bg-[#FF5C3D] px-4 py-2 text-sm font-semibold text-white">New spotlight</a>
  </div>
  <div class="space-y-3">
    @forelse($spotlights as $spotlight)
      <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div><p class="font-medium text-[#101E33]">{{ $spotlight->user?->name }} · {{ $spotlight->month }}</p><p class="mt-1 text-sm text-slate-600">{{ $spotlight->quote }}</p></div>
          <div class="flex items-center gap-3"><a href="{{ route('admin.spotlights.edit', $spotlight) }}" class="text-sm text-[#C23A1F] hover:underline">Edit</a><form method="POST" action="{{ route('admin.spotlights.destroy', $spotlight) }}" onsubmit="return confirm('Delete this spotlight?')">@csrf @method('DELETE')<button class="text-sm text-red-600 hover:underline">Delete</button></form></div>
        </div>
      </article>
    @empty
      <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">No spotlights yet.</div>
    @endforelse
  </div>
  <div class="mt-4">{{ $spotlights->links() }}</div>
</div>
@endsection