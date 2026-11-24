@extends('layouts.admin')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div><h1 class="font-display text-2xl font-extrabold text-[#101E33]">Micro-grants</h1><p class="mt-1 text-sm text-slate-500">Create grant rounds and review applications.</p></div>
    <a href="{{ route('admin.grants.create') }}" class="rounded-lg bg-[#FF5C3D] px-4 py-2 text-sm font-semibold text-white">New grant</a>
  </div>
  <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-slate-500"><tr><th class="p-3 text-left">Grant</th><th class="p-3 text-left">Amount</th><th class="p-3 text-left">Closes</th><th class="p-3 text-left">Applications</th><th class="p-3 text-left">Status</th><th class="p-3"></th></tr></thead>
      <tbody>
        @forelse($grants as $grant)
          <tr class="border-t border-slate-100">
            <td class="p-3 font-medium text-[#101E33]">{{ $grant->title }}</td>
            <td class="p-3 text-slate-600">{{ $grant->amount ? '₦'.number_format($grant->amount) : '—' }}</td>
            <td class="p-3 text-slate-600">{{ $grant->closes_at?->format('M j, Y') ?? 'No deadline' }}</td>
            <td class="p-3 text-slate-600">{{ $grant->applications_count }}</td>
            <td class="p-3"><span class="rounded-full px-2 py-0.5 text-xs {{ $grant->status === 'open' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($grant->status) }}</span></td>
            <td class="p-3 whitespace-nowrap"><a href="{{ route('admin.grants.applications', $grant) }}" class="text-[#C23A1F] hover:underline">Applications</a><a href="{{ route('admin.grants.edit', $grant) }}" class="ml-3 text-slate-600 hover:underline">Edit</a></td>
          </tr>
        @empty
          <tr><td colspan="6" class="p-8 text-center text-slate-500">No grants yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $grants->links() }}</div>
</div>
@endsection