@extends('layouts.app')
@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $badge = [
    'draft' => 'bg-slate-100 text-slate-700',
    'sending' => 'bg-amber-50 text-amber-800',
    'sent' => 'bg-green-50 text-green-700',
    'failed' => 'bg-red-50 text-red-700',
  ];
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
      <a href="{{ route('admin.mail.index') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }}">Back to mail center</a>
      <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Broadcasts</h1>
      <p class="mt-1 text-sm text-slate-500">Send one message to a group. Each recipient gets an individual email.</p>
    </div>
    <a href="{{ route('admin.mail.broadcasts.create') }}" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition text-center {{ $focus }}">New broadcast</a>
  </div>

  @include('admin.mail._nav')

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
    <table class="w-full min-w-[820px] text-sm">
      <caption class="sr-only">Email broadcasts</caption>
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th scope="col" class="text-left p-3 font-medium">Broadcast</th>
          <th scope="col" class="text-left p-3 font-medium">Audience</th>
          <th scope="col" class="text-left p-3 font-medium">Recipients</th>
          <th scope="col" class="text-left p-3 font-medium">Status</th>
          <th scope="col" class="text-left p-3 font-medium">Created</th>
        </tr>
      </thead>
      <tbody>
        @forelse($broadcasts as $broadcast)
          <tr class="border-t border-slate-100 hover:bg-slate-50/60">
            <td class="p-3">
              <a href="{{ route('admin.mail.broadcasts.show', $broadcast) }}" class="font-medium text-[#101E33] hover:text-[#FF5C3D] hover:underline {{ $focus }}">{{ $broadcast->name }}</a>
              <div class="text-xs text-slate-500">{{ $broadcast->subject }}</div>
            </td>
            <td class="p-3 text-slate-700">{{ $broadcast->audienceLabel() }}</td>
            <td class="p-3 text-slate-700">
              {{ $broadcast->recipients_count }}
              @if($broadcast->sent_count || $broadcast->failed_count)
                <div class="text-xs text-slate-500">{{ $broadcast->sent_count }} sent · {{ $broadcast->failed_count }} failed</div>
              @endif
            </td>
            <td class="p-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badge[$broadcast->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($broadcast->status) }}</span>
            </td>
            <td class="p-3 text-slate-600 whitespace-nowrap">
              {{ $broadcast->created_at?->format('M j, Y') }}
              <div class="text-xs text-slate-500">{{ $broadcast->creator?->name }}</div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="p-8 text-center text-slate-500">No broadcasts yet. <a href="{{ route('admin.mail.broadcasts.create') }}" class="text-[#FF5C3D] hover:underline">Create the first broadcast</a>.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <nav class="mt-4" aria-label="Pagination">{{ $broadcasts->links() }}</nav>
</div>
@endsection
