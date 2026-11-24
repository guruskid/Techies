@extends('layouts.admin')
@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $btn = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold border transition '.$focus;
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
      <a href="{{ route('admin.mail.index') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }}">Back to mail center</a>
      <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Connected mailboxes</h1>
      <p class="mt-1 text-sm text-slate-500">Connect as many mailboxes as you need. One is the default sender; any of them can receive replies.</p>
    </div>
    <a href="{{ route('admin.mail.accounts.create') }}" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition text-center {{ $focus }}">Connect mailbox</a>
  </div>

  @include('admin.mail._nav')

  <div class="space-y-3">
    @forelse($accounts as $account)
      <article class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <h2 class="font-bold text-[#101E33]">{{ $account->name }}</h2>
              @if($account->is_default)
                <span class="px-2 py-0.5 rounded-full text-[11px] bg-[#FF5C3D]/10 text-[#FF5C3D] font-semibold">Default sender</span>
              @endif
              <span class="px-2 py-0.5 rounded-full text-[11px] {{ $account->is_active ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }}">{{ $account->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <p class="text-sm text-slate-600 break-all">{{ $account->email }}</p>
            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
              <span>SMTP: {{ $account->smtp_host ? $account->smtp_host.':'.$account->smtp_port : 'not set' }}</span>
              <span>IMAP: {{ $account->imap_host ? $account->imap_host.':'.$account->imap_port : 'not set' }}</span>
              <span>{{ $account->messages_count }} message(s)</span>
              <span>{{ $account->broadcasts_count }} broadcast(s)</span>
              @if($account->last_synced_at)
                <span>Synced {{ $account->last_synced_at->diffForHumans() }}</span>
              @endif
            </div>
            @if($account->last_sync_error)
              <p class="mt-2 text-xs text-red-600 break-words">{{ \Illuminate\Support\Str::limit($account->last_sync_error, 180) }}</p>
            @endif
          </div>

          <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.mail.accounts.edit', $account) }}" class="{{ $btn }} border-[#101E33] bg-[#101E33] text-white hover:opacity-90">Edit</a>
            @unless($account->is_default)
              <form method="POST" action="{{ route('admin.mail.accounts.default', $account) }}">
                @csrf
                <button type="submit" class="{{ $btn }} border-slate-200 bg-white text-slate-700 hover:bg-slate-50">Make default</button>
              </form>
            @endunless
            @if($account->canReceive())
              <form method="POST" action="{{ route('admin.mail.accounts.sync', $account) }}">
                @csrf
                <button type="submit" class="{{ $btn }} border-slate-200 bg-white text-slate-700 hover:bg-slate-50">Sync inbox</button>
              </form>
            @endif
            <a href="{{ route('admin.mail.inbox', ['account' => $account->id]) }}" class="{{ $btn }} border-slate-200 bg-white text-slate-700 hover:bg-slate-50">Inbox</a>
          </div>
        </div>
      </article>
    @empty
      <div class="rounded-2xl border border-slate-100 bg-white p-8 text-center">
        <p class="font-semibold text-[#101E33]">No mailboxes connected</p>
        <p class="mt-1 text-sm text-slate-500">Add an SMTP account for sending and, optionally, its IMAP details so replies appear in the inbox.</p>
        <a href="{{ route('admin.mail.accounts.create') }}" class="mt-4 inline-block px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 {{ $focus }}">Connect the first mailbox</a>
      </div>
    @endforelse
  </div>
</div>
@endsection
