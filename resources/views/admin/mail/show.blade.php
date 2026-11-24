@extends('layouts.admin')
@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $isInbound = $message->isInbound();
  $backRoute = $isInbound ? 'admin.mail.inbox' : 'admin.mail.sent';
@endphp

<div class="max-w-5xl mx-auto px-4 py-8 pb-12">
  <div class="mb-4">
    <a href="{{ route($backRoute) }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }}">← Back to {{ $isInbound ? 'inbox' : 'sent mail' }}</a>
  </div>

  @include('admin.mail._nav')

  <article class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
    <header class="border-b border-slate-100 pb-4">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
          <h1 class="text-xl font-bold text-[#101E33] break-words">{{ $message->subject ?: '(no subject)' }}</h1>
          <p class="mt-2 text-sm text-slate-600">
            <span class="font-medium text-slate-800">{{ $isInbound ? 'From' : 'To' }}:</span>
            @if($isInbound)
              {{ $message->from_name ? $message->from_name.' <'.$message->from_email.'>' : $message->from_email }}
            @else
              {{ $message->to_email }}
              @if($message->cc) · cc {{ $message->cc }} @endif
              @if($message->bcc) · bcc {{ $message->bcc }} @endif
            @endif
          </p>
          <p class="mt-1 text-xs text-slate-500">
            {{ $message->account?->name }}
            · {{ ($isInbound ? $message->received_at : $message->sent_at)?->format('D, M j, Y H:i') ?? $message->created_at?->format('D, M j, Y H:i') }}
            · {{ ucfirst($message->status) }}
          </p>
        </div>
        <div class="flex flex-wrap gap-2">
          @if($isInbound)
            <a href="{{ route('admin.mail.compose', ['reply' => $message->id]) }}"
               class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition {{ $focus }}">Reply</a>
          @endif
        </div>
      </div>

      @if(! $isInbound && $message->status === 'failed' && $message->error)
        <div class="mt-3 rounded-lg bg-red-50 border border-red-100 p-3 text-sm text-red-700">{{ $message->error }}</div>
      @endif
    </header>

    <div class="pt-5">
      @if($message->attachments->isNotEmpty())
        <section class="mb-5 rounded-xl border border-slate-200 bg-slate-50 p-4" aria-labelledby="attachments-heading">
          <h2 id="attachments-heading" class="text-sm font-bold text-[#101E33]">Attachments ({{ $message->attachments->count() }})</h2>
          <ul class="mt-2 space-y-2">
            @foreach($message->attachments as $attachment)
              <li>
                <a href="{{ $attachment->url() }}" class="flex flex-wrap items-center justify-between gap-2 text-sm text-[#FF5C3D] hover:underline {{ $focus }}">
                  <span class="break-all">{{ $attachment->filename }}</span>
                  <span class="text-xs text-slate-500">{{ $attachment->humanSize() }}</span>
                </a>
              </li>
            @endforeach
          </ul>
        </section>
      @endif

      @if($message->body_html)
        <iframe title="Message body" sandbox
                srcdoc="{{ $message->body_html }}"
                class="w-full min-h-[28rem] border border-slate-200 rounded-xl bg-white"></iframe>
      @elseif($message->body_text)
        <div class="text-sm text-slate-800 whitespace-pre-line leading-relaxed">{{ $message->body_text }}</div>
      @else
        <p class="text-sm text-slate-500">This message has no readable body.</p>
      @endif
    </div>
  </article>

  @if($thread->isNotEmpty())
    <section class="mt-6" aria-labelledby="thread-heading">
      <h2 id="thread-heading" class="text-sm font-bold text-[#101E33] mb-2">Related messages</h2>
      <ul class="space-y-2">
        @foreach($thread as $related)
          <li>
            <a href="{{ route($related->isInbound() ? 'admin.mail.inbox.show' : 'admin.mail.sent.show', $related) }}"
               class="block bg-white rounded-xl border border-slate-100 p-3 hover:border-[#FF5C3D] transition {{ $focus }}">
              <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="text-sm font-medium text-[#101E33]">{{ $related->subject ?: '(no subject)' }}</span>
                <span class="text-xs text-slate-500">{{ $related->created_at?->format('M j, Y H:i') }}</span>
              </div>
              <div class="text-xs text-slate-500 mt-0.5">
                {{ $related->isInbound() ? 'From '.$related->from_email : 'To '.$related->to_email }}
              </div>
            </a>
          </li>
        @endforeach
      </ul>
    </section>
  @endif
</div>
@endsection
