@extends('layouts.admin')
@section('content')
@php
  $card = 'bg-white rounded-2xl border border-slate-100 shadow-sm p-5';
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
      <a href="{{ route('admin.manage') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }}">Back to management center</a>
      <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Mail center</h1>
      <p class="mt-1 text-sm text-slate-500">Send from connected mailboxes, receive replies, and broadcast to the community.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('admin.mail.compose') }}" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition text-center {{ $focus }}">Compose</a>
      <a href="{{ route('admin.mail.broadcasts.create') }}" class="px-4 py-2 border border-[#101E33] text-[#101E33] rounded-lg text-sm font-semibold hover:bg-slate-50 transition text-center {{ $focus }}">New broadcast</a>
    </div>
  </div>

  @include('admin.mail._nav')

  @if($accounts->isEmpty())
    <div class="rounded-2xl border border-[#FF5C3D]/30 bg-[#FF5C3D]/5 p-6 text-sm text-slate-700">
      <p class="font-semibold text-[#101E33]">No mailbox connected yet.</p>
      <p class="mt-1">Connect an email account with SMTP settings to send, and optionally IMAP to receive replies inside the platform.</p>
      <a href="{{ route('admin.mail.accounts.create') }}" class="mt-3 inline-block px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 {{ $focus }}">Connect a mailbox</a>
    </div>
  @endif

  <dl class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-3">
    @foreach([
      ['Mailboxes', $accounts->count(), 'admin.mail.accounts.index'],
      ['Unread replies', $unreadCount, 'admin.mail.inbox'],
      ['Sent this month', $sentThisMonth, 'admin.mail.sent'],
      ['Broadcasts', $broadcastCount, 'admin.mail.broadcasts.index'],
    ] as [$label, $value, $link])
      <a href="{{ route($link) }}" class="{{ $card }} hover:border-[#FF5C3D] transition {{ $focus }}">
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</dt>
        <dd class="mt-1 text-3xl font-bold text-[#101E33]">{{ $value }}</dd>
      </a>
    @endforeach
  </dl>

  <div class="mt-6 grid lg:grid-cols-2 gap-6">
    <section class="{{ $card }}" aria-labelledby="recent-inbox">
      <div class="flex items-center justify-between gap-3">
        <h2 id="recent-inbox" class="font-bold text-[#101E33]">Latest replies</h2>
        <a href="{{ route('admin.mail.inbox') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }}">Open inbox</a>
      </div>
      <ul class="mt-3 divide-y divide-slate-100">
        @forelse($recentInbox as $message)
          <li class="py-3">
            <a href="{{ route('admin.mail.inbox.show', $message) }}" class="block {{ $focus }}">
              <div class="flex items-start justify-between gap-3">
                <span class="text-sm font-medium text-[#101E33] {{ $message->isUnread() ? 'font-bold' : '' }}">{{ $message->from_name ?: $message->from_email }}</span>
                <span class="text-xs text-slate-400 whitespace-nowrap">{{ $message->received_at?->diffForHumans() }}</span>
              </div>
              <div class="text-sm text-slate-700 mt-0.5">{{ $message->subject }}</div>
              <div class="text-xs text-slate-500 mt-0.5">{{ $message->preview(90) }}</div>
            </a>
          </li>
        @empty
          <li class="py-6 text-sm text-slate-500">No replies yet. Connect IMAP or use the inbound webhook address to receive mail.</li>
        @endforelse
      </ul>
    </section>

    <section class="{{ $card }}" aria-labelledby="recent-sent">
      <div class="flex items-center justify-between gap-3">
        <h2 id="recent-sent" class="font-bold text-[#101E33]">Recently sent</h2>
        <a href="{{ route('admin.mail.sent') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }}">View sent</a>
      </div>
      <ul class="mt-3 divide-y divide-slate-100">
        @forelse($recentSent as $message)
          <li class="py-3">
            <a href="{{ route('admin.mail.sent.show', $message) }}" class="block {{ $focus }}">
              <div class="flex items-start justify-between gap-3">
                <span class="text-sm font-medium text-[#101E33]">{{ $message->subject }}</span>
                <span class="text-xs text-slate-400 whitespace-nowrap">{{ $message->sent_at?->diffForHumans() ?? $message->created_at?->diffForHumans() }}</span>
              </div>
              <div class="text-xs text-slate-500 mt-0.5">To {{ $message->to_email }} · {{ ucfirst($message->status) }}</div>
            </a>
          </li>
        @empty
          <li class="py-6 text-sm text-slate-500">Nothing sent yet. Use Compose to email a member or organization.</li>
        @endforelse
      </ul>
    </section>
  </div>

  @if($accounts->isNotEmpty())
    <section class="mt-6 {{ $card }}" aria-labelledby="mailboxes">
      <div class="flex items-center justify-between gap-3">
        <h2 id="mailboxes" class="font-bold text-[#101E33]">Connected mailboxes</h2>
        <a href="{{ route('admin.mail.accounts.index') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }}">Manage</a>
      </div>
      <ul class="mt-3 grid sm:grid-cols-2 gap-3">
        @foreach($accounts as $account)
          <li class="rounded-xl border border-slate-100 p-3">
            <div class="flex items-start justify-between gap-2">
              <div>
                <p class="font-medium text-[#101E33]">{{ $account->name }}</p>
                <p class="text-xs text-slate-500 break-all">{{ $account->email }}</p>
              </div>
              <div class="flex flex-col items-end gap-1">
                @if($account->is_default)<span class="px-2 py-0.5 rounded-full text-[11px] bg-[#FF5C3D]/10 text-[#FF5C3D] font-semibold">Default</span>@endif
                @unless($account->is_active)<span class="px-2 py-0.5 rounded-full text-[11px] bg-slate-100 text-slate-600">Inactive</span>@endunless
              </div>
            </div>
            <div class="mt-2 flex flex-wrap gap-2 text-[11px] text-slate-500">
              <span class="px-2 py-0.5 rounded-full {{ $account->smtp_host ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">SMTP {{ $account->smtp_host ? 'connected' : 'not set' }}</span>
              <span class="px-2 py-0.5 rounded-full {{ $account->imap_host ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">IMAP {{ $account->imap_host ? 'connected' : 'not set' }}</span>
            </div>
          </li>
        @endforeach
      </ul>
    </section>
  @endif
</div>
@endsection
