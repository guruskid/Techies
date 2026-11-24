@extends('layouts.admin')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 pb-12">
  <a href="{{ route('admin.grants.index') }}" class="text-sm text-[#C23A1F] hover:underline">Back to grants</a>
  <h1 class="mt-2 font-display text-2xl font-extrabold text-[#101E33]">{{ $grant->title }} applications</h1>
  <div class="mt-5 space-y-4">
    @forelse($applications as $application)
      <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <p class="font-medium text-[#101E33]">{{ $application->project_title }}</p>
            <p class="text-xs text-slate-500">{{ $application->user?->name }} · {{ $application->user?->email }}</p>
          </div>
          <span class="rounded-full px-2 py-0.5 text-xs {{ $application->status === 'approved' ? 'bg-green-50 text-green-700' : ($application->status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">{{ ucfirst($application->status) }}</span>
        </div>
        <p class="mt-3 text-sm text-slate-600 whitespace-pre-line">{{ $application->summary }}</p>
        @if($application->budget_breakdown)<p class="mt-2 text-sm text-slate-500 whitespace-pre-line">{{ $application->budget_breakdown }}</p>@endif
        <form method="POST" action="{{ route('admin.grants.review', [$grant, $application]) }}" class="mt-4 flex flex-wrap items-end gap-2 border-t border-slate-100 pt-4">
          @csrf
          <select name="status" class="rounded-lg border border-slate-200 px-3 py-2 text-sm"><option value="approved">Approve</option><option value="rejected">Reject</option></select>
          <input name="review_notes" class="min-w-0 flex-1 rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Notes to the member">
          <button class="rounded-lg bg-[#FF5C3D] px-4 py-2 text-sm font-semibold text-white">Save</button>
        </form>
      </article>
    @empty
      <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">No applications yet.</div>
    @endforelse
  </div>
</div>
@endsection