@extends('layouts.admin')
@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $field = 'border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <div class="mb-2">
    <a href="{{ route('admin.mail.index') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }}">Back to mail center</a>
    <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Inbox</h1>
    <p class="mt-1 text-sm text-slate-500">{{ $unreadCount }} unread {{ \Illuminate\Support\Str::plural('reply', $unreadCount) }}. Messages arrive through IMAP sync or each mailbox's inbound webhook.</p>
  </div>

  @include('admin.mail._nav')

  <form method="GET" action="{{ route('admin.mail.inbox') }}" role="search" class="flex flex-wrap items-end gap-2 mb-4">
    <div>
      <label for="search" class="block text-sm font-medium text-slate-700 mb-1">Search replies</label>
      <input id="search" type="search" name="search" value="{{ $search }}" placeholder="Subject, name or email" class="{{ $field }} w-64 max-w-full">
    </div>
    <div>
      <label for="account" class="block text-sm font-medium text-slate-700 mb-1">Mailbox</label>
      <select id="account" name="account" class="{{ $field }}">
        <option value="">All mailboxes</option>
        @foreach($accounts as $account)
          <option value="{{ $account->id }}" @selected($accountFilter === $account->id)>{{ $account->name }}</option>
        @endforeach
      </select>
    </div>
    <label class="flex items-center gap-2 text-sm text-slate-700 py-2">
      <input type="checkbox" name="unread" value="1" @checked($unreadOnly) class="rounded text-[#FF5C3D] focus:ring-[#FF5C3D]">
      Unread only
    </label>
    <button type="submit" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-medium hover:opacity-90 transition {{ $focus }}">Filter</button>
    @if($search !== '' || $accountFilter || $unreadOnly)
      <a href="{{ route('admin.mail.inbox') }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm hover:bg-slate-50 {{ $focus }}">Clear</a>
    @endif
  </form>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <ul class="divide-y divide-slate-100">
      @forelse($messages as $message)
        <li class="{{ $message->isUnread() ? 'bg-[#FF5C3D]/[0.03]' : '' }}">
          <a href="{{ route('admin.mail.inbox.show', $message) }}" class="block p-4 hover:bg-slate-50 transition {{ $focus }}">
            <div class="flex flex-wrap items-start justify-between gap-2">
              <div class="min-w-0">
                <p class="text-sm {{ $message->isUnread() ? 'font-bold text-[#101E33]' : 'font-medium text-slate-800' }}">
                  {{ $message->from_name ?: $message->from_email }}
                  <span class="font-normal text-xs text-slate-500">&lt;{{ $message->from_email }}&gt;</span>
                </p>
                <p class="mt-0.5 text-sm text-slate-700">{{ $message->subject }}</p>
                <p class="mt-0.5 text-xs text-slate-500">{{ $message->preview(120) }}</p>
              </div>
              <div class="text-right shrink-0">
                <p class="text-xs text-slate-400">{{ $message->received_at?->diffForHumans() }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $message->account?->name }}</p>
                @if($message->isUnread())
                  <span class="mt-1 inline-block px-2 py-0.5 rounded-full text-[11px] bg-[#FF5C3D] text-white font-semibold">New</span>
                @endif
              </div>
            </div>
          </a>
        </li>
      @empty
        <li class="p-8 text-center text-sm text-slate-500">
          No replies found. Set up IMAP on a mailbox and run <code class="px-1 py-0.5 bg-slate-100 rounded">php artisan mail:sync</code>, or copy the mailbox's inbound webhook URL from its settings.
        </li>
      @endforelse
    </ul>
  </div>

  <nav class="mt-4" aria-label="Pagination">{{ $messages->links() }}</nav>
</div>
@endsection
