@extends('layouts.app')
@section('content')
@php
  $editing = $account->exists;
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $input = 'w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $label = 'block text-sm font-medium text-slate-700 mb-1';
  $hint = 'mt-1 text-xs text-slate-500';
@endphp

<div class="max-w-4xl mx-auto px-4 py-8 pb-12">
  <div class="mb-2">
    <a href="{{ route('admin.mail.accounts.index') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }}">Back to mailboxes</a>
    <h1 class="mt-2 text-2xl font-bold text-[#101E33]">{{ $editing ? 'Edit mailbox' : 'Connect a mailbox' }}</h1>
    <p class="mt-1 text-sm text-slate-500">
      SMTP is used to send. IMAP pulls replies into the inbox, and the inbound webhook URL lets providers forward mail to the platform.
    </p>
  </div>

  @include('admin.mail._nav')

  @if($errors->any())
    <div class="mb-5 rounded-xl bg-red-50 border border-red-100 p-4 text-sm text-red-700">
      <ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ $editing ? route('admin.mail.accounts.update', $account) : route('admin.mail.accounts.store') }}"
        class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-6">
    @csrf
    @if($editing) @method('PATCH') @endif

    <section class="space-y-4" aria-labelledby="identity">
      <h2 id="identity" class="text-lg font-bold text-[#101E33]">Identity</h2>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label for="name" class="{{ $label }}">Mailbox label</label>
          <input id="name" name="name" required maxlength="100" value="{{ old('name', $account->name) }}" placeholder="Community team" class="{{ $input }}">
        </div>
        <div>
          <label for="email" class="{{ $label }}">Email address</label>
          <input id="email" name="email" type="email" required value="{{ old('email', $account->email) }}" placeholder="hello@example.org" class="{{ $input }}">
        </div>
        <div>
          <label for="from_name" class="{{ $label }}">Display name <span class="font-normal text-slate-400">(optional)</span></label>
          <input id="from_name" name="from_name" value="{{ old('from_name', $account->from_name) }}" placeholder="MWTT Community" class="{{ $input }}">
        </div>
        <div>
          <label for="reply_to" class="{{ $label }}">Reply-To <span class="font-normal text-slate-400">(optional)</span></label>
          <input id="reply_to" name="reply_to" type="email" value="{{ old('reply_to', $account->reply_to) }}" class="{{ $input }}">
        </div>
      </div>
      <div>
        <label for="signature" class="{{ $label }}">Signature <span class="font-normal text-slate-400">(optional)</span></label>
        <textarea id="signature" name="signature" rows="3" maxlength="2000" class="{{ $input }}">{{ old('signature', $account->signature) }}</textarea>
        <p class="{{ $hint }}">Appended to every message sent from this mailbox.</p>
      </div>
    </section>

    <section class="space-y-4 border-t border-slate-100 pt-5" aria-labelledby="sending">
      <div>
        <h2 id="sending" class="text-lg font-bold text-[#101E33]">Sending (SMTP)</h2>
        <p class="mt-1 text-sm text-slate-500">Use the SMTP details from your mail provider. Port 587 normally uses TLS; port 465 uses SSL.</p>
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label for="smtp_host" class="{{ $label }}">SMTP host</label>
          <input id="smtp_host" name="smtp_host" value="{{ old('smtp_host', $account->smtp_host) }}" placeholder="smtp.example.org" class="{{ $input }}">
        </div>
        <div>
          <label for="smtp_port" class="{{ $label }}">SMTP port</label>
          <input id="smtp_port" name="smtp_port" type="number" min="1" max="65535" value="{{ old('smtp_port', $account->smtp_port ?: 587) }}" class="{{ $input }}">
        </div>
        <div>
          <label for="smtp_username" class="{{ $label }}">SMTP username</label>
          <input id="smtp_username" name="smtp_username" value="{{ old('smtp_username', $account->smtp_username) }}" autocomplete="off" class="{{ $input }}">
        </div>
        <div>
          <label for="smtp_password" class="{{ $label }}">SMTP password</label>
          <input id="smtp_password" name="smtp_password" type="password" autocomplete="new-password" class="{{ $input }}"
                 placeholder="{{ $editing && $account->smtp_password ? 'Leave blank to keep the saved password' : '' }}">
        </div>
        <div>
          <label for="smtp_encryption" class="{{ $label }}">Encryption</label>
          <select id="smtp_encryption" name="smtp_encryption" class="{{ $input }}">
            @foreach(['tls' => 'TLS / STARTTLS', 'ssl' => 'SSL', 'none' => 'None'] as $value => $option)
              <option value="{{ $value }}" @selected(old('smtp_encryption', $account->smtp_encryption ?: 'tls') === $value)>{{ $option }}</option>
            @endforeach
          </select>
        </div>
        <label class="flex items-start gap-3 text-sm text-slate-700 sm:pt-7">
          <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editing ? $account->is_active : true)) class="mt-0.5 rounded text-[#FF5C3D] focus:ring-[#FF5C3D]">
          <span>Account is active
            <span class="block text-xs text-slate-500">Inactive accounts cannot send or receive.</span>
          </span>
        </label>
      </div>
      @unless($editing)
        <label class="flex items-start gap-3 text-sm text-slate-700">
          <input type="checkbox" name="is_default" value="1" class="mt-0.5 rounded text-[#FF5C3D] focus:ring-[#FF5C3D]">
          <span>Make this the default sending account</span>
        </label>
      @endunless
    </section>

    <section class="space-y-4 border-t border-slate-100 pt-5" aria-labelledby="receiving">
      <div>
        <h2 id="receiving" class="text-lg font-bold text-[#101E33]">Receiving (IMAP)</h2>
        <p class="mt-1 text-sm text-slate-500">Optional. When configured, the scheduler fetches new replies into the inbox every ten minutes.</p>
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label for="imap_host" class="{{ $label }}">IMAP host</label>
          <input id="imap_host" name="imap_host" value="{{ old('imap_host', $account->imap_host) }}" placeholder="imap.example.org" class="{{ $input }}">
        </div>
        <div>
          <label for="imap_port" class="{{ $label }}">IMAP port</label>
          <input id="imap_port" name="imap_port" type="number" min="1" max="65535" value="{{ old('imap_port', $account->imap_port ?: 993) }}" class="{{ $input }}">
        </div>
        <div>
          <label for="imap_username" class="{{ $label }}">IMAP username</label>
          <input id="imap_username" name="imap_username" value="{{ old('imap_username', $account->imap_username) }}" autocomplete="off" class="{{ $input }}">
        </div>
        <div>
          <label for="imap_password" class="{{ $label }}">IMAP password</label>
          <input id="imap_password" name="imap_password" type="password" autocomplete="new-password" class="{{ $input }}"
                 placeholder="{{ $editing && $account->imap_password ? 'Leave blank to keep the saved password' : '' }}">
        </div>
        <div>
          <label for="imap_encryption" class="{{ $label }}">Encryption</label>
          <select id="imap_encryption" name="imap_encryption" class="{{ $input }}">
            @foreach(['ssl' => 'SSL', 'tls' => 'TLS / STARTTLS', 'none' => 'None'] as $value => $option)
              <option value="{{ $value }}" @selected(old('imap_encryption', $account->imap_encryption ?: 'ssl') === $value)>{{ $option }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <label class="flex items-start gap-3 text-sm text-slate-700">
        <input type="checkbox" name="requires_signature" value="1" @checked(old('requires_signature', $editing ? $account->requires_signature : false)) class="mt-0.5 rounded text-[#FF5C3D] focus:ring-[#FF5C3D]">
        <span>Require a signed inbound webhook
          <span class="block text-xs text-slate-500">Rejects webhook calls that do not include a valid HMAC-SHA256 signature. Leave off for simple forwarding rules.</span>
        </span>
      </label>
    </section>

    @if($editing)
      <label class="flex items-start gap-3 text-sm text-slate-700 border-t border-slate-100 pt-5">
        <input type="checkbox" name="is_default" value="1" @checked($account->is_default) class="mt-0.5 rounded text-[#FF5C3D] focus:ring-[#FF5C3D]">
        <span>Use as the default sending account</span>
      </label>

      <section class="space-y-4 border-t border-slate-100 pt-5" aria-labelledby="mailbox-access">
        <div>
          <h2 id="mailbox-access" class="text-lg font-bold text-[#101E33]">Mailbox access</h2>
          <p class="mt-1 text-sm text-slate-500">Super admins see every mailbox. You always manage the mailboxes you created. Invite other admins to view or manage this one.</p>
        </div>

        @if(($admins ?? collect())->isEmpty())
          <p class="text-sm text-slate-500">There are no other active admins to invite.</p>
        @else
          <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200">
            @foreach($admins as $adminUser)
              <li class="flex flex-wrap items-center justify-between gap-3 p-3">
                <div class="min-w-0">
                  <div class="text-sm font-medium text-[#101E33]">{{ $adminUser->name }}</div>
                  <div class="text-xs text-slate-500 break-all">{{ $adminUser->email }}</div>
                </div>
                <div class="flex items-center gap-4 text-sm text-slate-700">
                  <label class="flex items-center gap-2">
                    <input type="checkbox" name="shared_users[]" value="{{ $adminUser->id }}"
                           @checked(in_array($adminUser->id, $sharedViewers ?? [], true))
                           class="rounded text-[#FF5C3D] focus:ring-[#FF5C3D]">
                    View
                  </label>
                  <label class="flex items-center gap-2">
                    <input type="checkbox" name="manager_users[]" value="{{ $adminUser->id }}"
                           @checked(in_array($adminUser->id, $sharedManagers ?? [], true))
                           class="rounded text-[#FF5C3D] focus:ring-[#FF5C3D]">
                    Manage
                  </label>
                </div>
              </li>
            @endforeach
          </ul>
          <p class="text-xs text-slate-500">Manage includes view. Ticking Manage is enough.</p>
        @endif
      </section>
    @endif

    <div class="flex flex-wrap gap-3 pt-1">
      <button type="submit" class="px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition {{ $focus }}">{{ $editing ? 'Save mailbox' : 'Connect mailbox' }}</button>
      <a href="{{ route('admin.mail.accounts.index') }}" class="px-5 py-2.5 border border-slate-200 rounded-lg font-medium hover:bg-slate-50 transition {{ $focus }}">Cancel</a>
    </div>
  </form>

  @if($editing)
    <div class="mt-6 grid lg:grid-cols-2 gap-4">
      <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5" aria-labelledby="test-mail">
        <h2 id="test-mail" class="font-bold text-[#101E33]">Send a test email</h2>
        <p class="mt-1 text-sm text-slate-500">Confirms the SMTP details are correct.</p>
        <form method="POST" action="{{ route('admin.mail.accounts.test', $account) }}" class="mt-3 flex flex-wrap gap-2">
          @csrf
          <label for="test_email" class="sr-only">Test recipient</label>
          <input id="test_email" name="test_email" type="email" required value="{{ old('test_email', auth()->user()->email) }}"
                 class="flex-1 min-w-[14rem] border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          <button type="submit" class="px-4 py-2 bg-[#101E33] text-white rounded-lg text-sm font-semibold hover:opacity-90 {{ $focus }}">Send test</button>
        </form>
      </section>

      <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5" aria-labelledby="inbound-url">
        <h2 id="inbound-url" class="font-bold text-[#101E33]">Inbound webhook</h2>
        <p class="mt-1 text-sm text-slate-500">Point Mailgun, Postmark, SendGrid or a forwarding rule at this URL to receive mail without IMAP.</p>
        <div class="mt-3 flex gap-2">
          <label for="inbound_url" class="sr-only">Inbound webhook URL</label>
          <input id="inbound_url" readonly value="{{ $account->inboundUrl() }}"
                 class="flex-1 min-w-0 border border-slate-200 rounded-lg px-3 py-2 text-xs bg-slate-50 text-slate-700">
          <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('inbound_url').value)"
                  class="px-3 py-2 border border-slate-200 rounded-lg text-sm hover:bg-slate-50 {{ $focus }}">Copy</button>
        </div>
        <p class="mt-2 text-xs text-slate-500">Treat this URL as a secret. Rotating it immediately replaces both the URL token and the signing secret.</p>

        <div class="mt-3">
          <label for="inbound_secret" class="{{ $label }}">Signing secret</label>
          <div class="flex gap-2">
            <input id="inbound_secret" readonly value="{{ $account->inbound_secret }}"
                   class="flex-1 min-w-0 border border-slate-200 rounded-lg px-3 py-2 text-xs bg-slate-50 text-slate-700 font-mono">
            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('inbound_secret').value)"
                    class="px-3 py-2 border border-slate-200 rounded-lg text-sm hover:bg-slate-50 {{ $focus }}">Copy</button>
          </div>
          <p class="mt-2 text-xs text-slate-500">Send it as <code class="px-1 bg-slate-100 rounded">X-Techies-Signature: sha256=&lt;hmac&gt;</code>, where the HMAC is SHA-256 of the raw request body.</p>
        </div>

        <form method="POST" action="{{ route('admin.mail.accounts.rotate-inbound', $account) }}" class="mt-3"
              onsubmit="return confirm('Rotate the inbound URL and signing secret? Your mail provider must be updated immediately.')">
          @csrf
          <button type="submit" class="px-3 py-2 border border-amber-200 text-amber-800 rounded-lg text-sm font-medium hover:bg-amber-50 {{ $focus }}">Rotate inbound credentials</button>
        </form>
      </section>
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
      <div class="flex flex-wrap gap-2">
        @if($account->canReceive())
          <form method="POST" action="{{ route('admin.mail.accounts.test-imap', $account) }}">
            @csrf
            <button type="submit" class="px-4 py-2 border border-slate-200 rounded-lg text-sm hover:bg-slate-50 {{ $focus }}">Test IMAP connection</button>
          </form>
          <form method="POST" action="{{ route('admin.mail.accounts.sync', $account) }}">
            @csrf
            <button type="submit" class="px-4 py-2 border border-slate-200 rounded-lg text-sm hover:bg-slate-50 {{ $focus }}">Sync inbox now</button>
          </form>
        @endif
      </div>
      <form method="POST" action="{{ route('admin.mail.accounts.destroy', $account) }}"
            onsubmit="return confirm('Remove this mailbox? Sent messages stay in the log, but it will no longer send or receive.')">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50 {{ $focus }}">Remove mailbox</button>
      </form>
    </div>

    @if(isset($inbox) && $inbox->isNotEmpty())
      <section class="mt-6 bg-white rounded-2xl border border-slate-100 shadow-sm p-5" aria-labelledby="recent-account-inbox">
        <div class="flex items-center justify-between gap-3">
          <h2 id="recent-account-inbox" class="font-bold text-[#101E33]">Recent mail</h2>
          <a href="{{ route('admin.mail.inbox', ['account' => $account->id]) }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }}">Open inbox</a>
        </div>
        <ul class="mt-2 divide-y divide-slate-100">
          @foreach($inbox as $message)
            <li class="py-2">
              <a href="{{ route('admin.mail.inbox.show', $message) }}" class="block {{ $focus }}">
                <div class="flex flex-wrap items-center justify-between gap-2">
                  <span class="text-sm text-slate-800">{{ $message->from_name ?: $message->from_email }}</span>
                  <span class="text-xs text-slate-500">{{ $message->received_at?->diffForHumans() }}</span>
                </div>
                <div class="text-xs text-slate-500">{{ $message->subject }}</div>
              </a>
            </li>
          @endforeach
        </ul>
      </section>
    @endif
  @endif
</div>
@endsection
