@extends('layouts.admin')
@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $badge = [
    'draft' => 'bg-slate-100 text-slate-700',
    'scheduled' => 'bg-sky-50 text-sky-800',
    'sending' => 'bg-amber-50 text-amber-800',
    'sent' => 'bg-green-50 text-green-700',
    'failed' => 'bg-red-50 text-red-700',
  ];
  $recipientBadge = [
    'pending' => 'bg-slate-100 text-slate-600',
    'sent' => 'bg-green-50 text-green-700',
    'failed' => 'bg-red-50 text-red-700',
  ];
@endphp

<div class="max-w-6xl mx-auto px-4 py-8 pb-12">
  <div class="mb-2">
    <a href="{{ route('admin.mail.broadcasts.index') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }}">Back to broadcasts</a>
    <div class="mt-2 flex flex-wrap items-start justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold text-[#101E33]">{{ $broadcast->name }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $broadcast->subject }}</p>
      </div>
      <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badge[$broadcast->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($broadcast->status) }}</span>
    </div>
  </div>

  @include('admin.mail._nav')

  <dl class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    @foreach([
      ['Recipients', $broadcast->recipients_count],
      ['Sent', $broadcast->sent_count],
      ['Failed', $broadcast->failed_count],
      ['Audience', $broadcast->audienceLabel()],
    ] as [$label, $value])
      <div class="bg-white rounded-xl border border-slate-100 p-4">
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</dt>
        <dd class="mt-1 text-lg font-bold text-[#101E33]">{{ $value }}</dd>
      </div>
    @endforeach
  </dl>

  <div class="grid lg:grid-cols-3 gap-6">
    <section class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6" aria-labelledby="preview-heading">
      <h2 id="preview-heading" class="font-bold text-[#101E33]">Message preview</h2>
      <p class="mt-1 text-xs text-slate-500">Merge tags are shown as written; recipients receive their own details.</p>
      <div class="mt-3 rounded-xl overflow-hidden border border-slate-200">
        <iframe title="Broadcast preview" sandbox srcdoc="{{ view('emails.mail-layout', ['content' => $broadcast->body, 'account' => $broadcast->account])->render() }}"
                class="w-full min-h-[30rem] bg-white"></iframe>
      </div>
    </section>

    <aside class="space-y-4">
      <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5" aria-labelledby="send-heading">
        <h2 id="send-heading" class="font-bold text-[#101E33]">Send</h2>
        <p class="mt-1 text-sm text-slate-500">
          {{ $broadcast->account?->name ?? 'No mailbox' }}
          @if($broadcast->account) · {{ $broadcast->account->email }} @endif
        </p>
        @if($broadcast->scheduled_at && $broadcast->status === 'scheduled')
          <p class="mt-2 rounded-lg bg-sky-50 text-sky-800 text-sm p-3">Scheduled for {{ $broadcast->scheduled_at->format('M j, Y H:i') }}.</p>
        @endif

        @if($broadcast->sent_at)
          <p class="mt-2 text-xs text-slate-500">Sent {{ $broadcast->sent_at->format('M j, Y H:i') }}.</p>
        @endif

        @if(in_array($broadcast->status, ['draft', 'failed', 'scheduled'], true))
          <form method="POST" action="{{ route('admin.mail.broadcasts.send', $broadcast) }}" class="mt-4"
                onsubmit="return confirm('Send this broadcast to {{ $broadcast->recipients_count }} recipient(s)?')">
            @csrf
            <button type="submit" class="w-full px-4 py-2.5 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition {{ $focus }}">{{ $broadcast->status === 'scheduled' ? 'Send now instead' : 'Send broadcast' }}</button>
          </form>
        @else
          <p class="mt-4 rounded-lg bg-green-50 text-green-700 text-sm p-3">This broadcast has been sent. Create a new broadcast to email the audience again.</p>
        @endif
      </section>

      <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 text-sm text-slate-600" aria-labelledby="details-heading">
        <h2 id="details-heading" class="font-bold text-[#101E33]">Details</h2>
        <dl class="mt-2 space-y-2">
          <div class="flex justify-between gap-3"><dt>Created by</dt><dd class="text-right">{{ $broadcast->creator?->name ?? '—' }}</dd></div>
          <div class="flex justify-between gap-3"><dt>Created</dt><dd class="text-right">{{ $broadcast->created_at?->format('M j, Y') }}</dd></div>
          <div class="flex justify-between gap-3"><dt>Scheduled</dt><dd class="text-right">{{ $broadcast->scheduled_at?->format('M j, Y H:i') ?? 'Not scheduled' }}</dd></div>
          <div class="flex justify-between gap-3"><dt>Template</dt><dd class="text-right">{{ $broadcast->template?->name ?? 'None' }}</dd></div>
        </dl>
        @if($broadcast->status !== 'sending')
          <form method="POST" action="{{ route('admin.mail.broadcasts.destroy', $broadcast) }}" class="mt-4"
                onsubmit="return confirm('Delete this broadcast and its recipient log?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50 {{ $focus }}">Delete broadcast</button>
          </form>
        @endif
      </section>
    </aside>
  </div>

  <section class="mt-6" aria-labelledby="recipients-heading">
    <h2 id="recipients-heading" class="text-sm font-bold text-[#101E33] mb-2">Recipients ({{ $broadcast->recipients_count }})</h2>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
      <table class="w-full min-w-[700px] text-sm">
        <caption class="sr-only">Broadcast recipients and delivery status</caption>
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th scope="col" class="text-left p-3 font-medium">Recipient</th>
            <th scope="col" class="text-left p-3 font-medium">Email</th>
            <th scope="col" class="text-left p-3 font-medium">Status</th>
            <th scope="col" class="text-left p-3 font-medium">Sent</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recipients as $recipient)
            <tr class="border-t border-slate-100">
              <td class="p-3 text-slate-800">{{ $recipient->name ?: ($recipient->user?->name ?? '—') }}</td>
              <td class="p-3 text-slate-600 break-all">{{ $recipient->email }}</td>
              <td class="p-3">
                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $recipientBadge[$recipient->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($recipient->status) }}</span>
                @if($recipient->error)
                  <div class="mt-1 text-xs text-red-600 max-w-[18rem] break-words">{{ \Illuminate\Support\Str::limit($recipient->error, 120) }}</div>
                @endif
              </td>
              <td class="p-3 text-slate-600 whitespace-nowrap">{{ $recipient->sent_at?->format('M j, H:i') ?? '—' }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="p-6 text-center text-slate-500">No recipients were captured for this broadcast.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <nav class="mt-4" aria-label="Pagination">{{ $recipients->links() }}</nav>
  </section>
</div>
@endsection
