@extends('layouts.admin')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-8 pb-12">
  <div class="mb-6">
    <h1 class="font-display text-2xl font-extrabold text-[#101E33]">Introductions</h1>
    <p class="mt-1 text-sm text-slate-500">Review member requests and make warm introductions.</p>
  </div>

  <div class="space-y-4">
    @forelse($requests as $intro)
      <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <p class="font-medium text-[#101E33]">{{ $intro->requester?->name }} → {{ $intro->target?->name ?? $intro->target_name ?? 'To be matched' }}</p>
            <p class="mt-1 text-sm text-slate-600">{{ $intro->purpose }}</p>
            @if($intro->context)<p class="mt-2 text-sm text-slate-500 whitespace-pre-line">{{ $intro->context }}</p>@endif
          </div>
          <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $intro->status === 'completed' ? 'bg-green-50 text-green-700' : ($intro->status === 'declined' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">{{ ucfirst($intro->status) }}</span>
        </div>

        @if($intro->status === 'pending')
          <form method="POST" action="{{ route('admin.introductions.update', $intro) }}" class="mt-4 grid sm:grid-cols-[minmax(0,1fr)_minmax(0,2fr)_auto] gap-2 items-end border-t border-slate-100 pt-4">
            @csrf
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Decision</label>
              <select name="status" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <option value="completed">Introduction made</option>
                <option value="declined">Decline</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Note to members</label>
              <input name="admin_notes" maxlength="2000" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Optional">
            </div>
            <button class="rounded-lg bg-[#FF5C3D] px-4 py-2 text-sm font-semibold text-white">Save</button>
          </form>
        @endif
      </article>
    @empty
      <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">No introduction requests yet.</div>
    @endforelse
  </div>
  <div class="mt-4">{{ $requests->links() }}</div>
</div>
@endsection