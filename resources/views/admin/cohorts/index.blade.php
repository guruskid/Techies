@extends('layouts.admin')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div><h1 class="font-display text-2xl font-extrabold text-[#101E33]">Cohorts</h1><p class="mt-1 text-sm text-slate-500">Structured learning cohorts and build sprints.</p></div>
    <a href="{{ route('admin.cohorts.create') }}" class="rounded-lg bg-[#FF5C3D] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e94f32]">New cohort</a>
  </div>
  <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-slate-500"><tr><th class="p-3 text-left">Cohort</th><th class="p-3 text-left">Track</th><th class="p-3 text-left">Dates</th><th class="p-3 text-left">Members</th><th class="p-3 text-left">Status</th><th class="p-3"></th></tr></thead>
      <tbody>
        @forelse($cohorts as $cohort)
          <tr class="border-t border-slate-100">
            <td class="p-3 font-medium text-[#101E33]">{{ $cohort->title }}</td>
            <td class="p-3 text-slate-600">{{ $cohort->track }}</td>
            <td class="p-3 text-slate-600">{{ $cohort->starts_at?->format('M j') }} – {{ $cohort->ends_at?->format('M j, Y') }}</td>
            <td class="p-3 text-slate-600">{{ $cohort->members_count }}</td>
            <td class="p-3"><span class="rounded-full px-2 py-0.5 text-xs {{ $cohort->status === 'open' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($cohort->status) }}</span></td>
            <td class="p-3 whitespace-nowrap">
              <a href="{{ route('admin.cohorts.edit', $cohort) }}" class="text-[#C23A1F] hover:underline">Edit</a>
              <form method="POST" action="{{ route('admin.cohorts.destroy', $cohort) }}" class="ml-3 inline" onsubmit="return confirm('Delete this cohort?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Delete</button></form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="p-8 text-center text-slate-500">No cohorts yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $cohorts->links() }}</div>
</div>
@endsection