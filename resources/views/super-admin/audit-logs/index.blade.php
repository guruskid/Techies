@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="mb-6">
    <a href="{{ route('super-admin.dashboard') }}" class="text-sm text-[#FF5C3D] hover:underline">← Super admin console</a>
    <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Audit logs</h1>
    <p class="mt-1 text-sm text-slate-500">Moderation, role and settings actions recorded across the platform.</p>
  </div>

  <form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input name="search" value="{{ request('search') }}" placeholder="Search by admin name or email" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
    <input name="action" value="{{ $action }}" placeholder="Action, e.g. content.approved" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
    <button class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm">Filter</button>
    @if(request()->hasAny(['search', 'action']))
      <a href="{{ route('super-admin.audit-logs.index') }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm">Clear</a>
    @endif
  </form>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-slate-500">
        <tr>
          <th class="text-left p-3">When</th>
          <th class="text-left p-3">Action</th>
          <th class="text-left p-3">Actor</th>
          <th class="text-left p-3">Target</th>
          <th class="text-left p-3">Details</th>
          <th class="text-left p-3">IP</th>
        </tr>
      </thead>
      <tbody>
        @forelse($logs as $log)
          <tr class="border-t border-slate-100 align-top">
            <td class="p-3 text-slate-500 whitespace-nowrap">{{ $log->created_at->format('M j, Y g:ia') }}</td>
            <td class="p-3 font-medium text-[#101E33]">{{ $log->action }}</td>
            <td class="p-3">{{ $log->user?->name ?? 'System' }}</td>
            <td class="p-3 text-slate-500">
              @if($log->model_type)
                {{ class_basename($log->model_type) }} #{{ $log->model_id }}
              @else
                —
              @endif
            </td>
            <td class="p-3 text-xs text-slate-500 max-w-xs break-words">
              @if($log->changes)
                {{ \Illuminate\Support\Str::limit(json_encode($log->changes), 160) }}
              @else
                —
              @endif
            </td>
            <td class="p-3 text-xs text-slate-400">{{ $log->ip_address ?? '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="p-6 text-center text-slate-500">No audit activity matches your filters.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection
