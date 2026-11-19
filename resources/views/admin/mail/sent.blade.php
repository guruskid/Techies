@extends('layouts.app')
@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $field = 'border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $badge = [
    'sent' => 'bg-green-50 text-green-700',
    'failed' => 'bg-red-50 text-red-700',
    'draft' => 'bg-amber-50 text-amber-700',
  ];
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <div class="mb-2">
    <a href="{{ route('admin.mail.index') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }}">Back to mail center</a>
    <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Sent mail</h1>
    <p class="mt-1 text-sm text-slate-500">Direct messages sent from your connected mailboxes.</p>
  </div>

  @include('admin.mail._nav')

  <form method="GET" action="{{ route('admin.mail.sent') }}" role="search" class="flex flex-wrap items-end gap-2 mb-4">
    <div>
      <label for="search" class="block text-sm font-medium text-slate-700 mb-1">Search sent mail</label>
      <input id="search" type="search" name="search" value="{{ $search }}" placeholder="Subject or recipient" class="{{ $field }} w-64 max-w-full">
    </div>
    <div>
      <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
      <select id="status" name="status" class="{{ $field }}">
        <option value="">All statuses</option>
        @foreach(['sent' => 'Sent', 'failed' => 'Failed', 'draft' => 'Draft'] as $value => $label)
          <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-medium hover:opacity-90 transition {{ $focus }}">Filter</button>
    @if($search !== '' || $status !== '')
      <a href="{{ route('admin.mail.sent') }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm hover:bg-slate-50 {{ $focus }}">Clear</a>
    @endif
  </form>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
    <table class="w-full min-w-[760px] text-sm">
      <caption class="sr-only">Messages sent from connected mailboxes</caption>
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th scope="col" class="text-left p-3 font-medium">Subject</th>
          <th scope="col" class="text-left p-3 font-medium">To</th>
          <th scope="col" class="text-left p-3 font-medium">Mailbox</th>
          <th scope="col" class="text-left p-3 font-medium">Sent</th>
          <th scope="col" class="text-left p-3 font-medium">Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($messages as $message)
          <tr class="border-t border-slate-100 hover:bg-slate-50/60">
            <td class="p-3">
              <a href="{{ route('admin.mail.sent.show', $message) }}" class="font-medium text-[#101E33] hover:text-[#FF5C3D] hover:underline {{ $focus }}">{{ $message->subject ?: '(no subject)' }}</a>
              <div class="text-xs text-slate-500">{{ $message->preview(80) }}</div>
            </td>
            <td class="p-3 text-slate-700">
              <div class="break-all">{{ $message->to_email }}</div>
              @if($message->cc)<div class="text-xs text-slate-500">cc {{ $message->cc }}</div>@endif
            </td>
            <td class="p-3 text-slate-700">{{ $message->account?->name ?? '—' }}</td>
            <td class="p-3 text-slate-700 whitespace-nowrap">
              {{ $message->sent_at?->format('M j, Y') ?? $message->created_at?->format('M j, Y') }}
              <div class="text-xs text-slate-500">{{ $message->sent_at?->format('H:i') }}</div>
            </td>
            <td class="p-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badge[$message->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($message->status) }}</span>
              @if($message->status === 'failed' && $message->error)
                <div class="mt-1 text-xs text-red-600 max-w-[16rem] break-words">{{ \Illuminate\Support\Str::limit($message->error, 120) }}</div>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="p-8 text-center text-slate-500">No sent mail yet. <a href="{{ route('admin.mail.compose') }}" class="text-[#FF5C3D] hover:underline">Compose a message</a>.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <nav class="mt-4" aria-label="Pagination">{{ $messages->links() }}</nav>
</div>
@endsection
