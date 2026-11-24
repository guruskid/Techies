@extends('layouts.admin')
@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $input = 'w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $label = 'block text-sm font-medium text-slate-700 mb-1';
  $draft = $draft ?? null;
  $subjectDefault = $replyTo
      ? (\Illuminate\Support\Str::startsWith($replyTo->subject, 'Re:') ? $replyTo->subject : 'Re: '.$replyTo->subject)
      : ($draft?->subject ?? $selectedTemplate?->subject ?? '');
@endphp

<div class="max-w-4xl mx-auto px-4 py-8 pb-12">
  <div class="mb-2">
    <a href="{{ route('admin.mail.index') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }}">Back to mail center</a>
    <h1 class="mt-2 text-2xl font-bold text-[#101E33]">{{ $replyTo ? 'Reply to message' : 'Compose message' }}</h1>
    <p class="mt-1 text-sm text-slate-500">Send from a connected mailbox. The recipient sees that mailbox as the sender and can reply straight back.</p>
  </div>

  @include('admin.mail._nav')

  @if($accounts->isEmpty())
    <div class="rounded-2xl border border-[#FF5C3D]/30 bg-[#FF5C3D]/5 p-6 text-sm text-slate-700">
      <p class="font-semibold text-[#101E33]">Connect a mailbox before sending.</p>
      <a href="{{ route('admin.mail.accounts.create') }}" class="mt-3 inline-block px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 {{ $focus }}">Connect a mailbox</a>
    </div>
  @else
    @if($errors->any())
      <div class="mb-5 rounded-xl bg-red-50 border border-red-100 p-4 text-sm text-red-700">
        <ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
      </div>
    @endif

    <form id="compose-form" method="POST" action="{{ route('admin.mail.send') }}" enctype="multipart/form-data" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-5" x-data>
      @csrf
      <input type="hidden" name="in_reply_to" value="{{ old('in_reply_to', $replyTo?->message_id ?? $draft?->in_reply_to) }}">

      <div class="grid sm:grid-cols-2 gap-5">
        <div>
          <label for="email_account_id" class="{{ $label }}">Send from</label>
          <select id="email_account_id" name="email_account_id" required class="{{ $input }}">
            @foreach($accounts as $account)
              <option value="{{ $account->id }}" @selected((int) old('email_account_id', $replyTo?->email_account_id ?? $draft?->email_account_id ?? $defaultAccount?->id) === $account->id)>
                {{ $account->name }} — {{ $account->email }}
              </option>
            @endforeach
          </select>
        </div>
        <div>
          <label for="template" class="{{ $label }}">Start from a template</label>
          <select id="template" class="{{ $input }}"
                  @change="
                    const option = $event.target.selectedOptions[0];
                    if (option.dataset.subject) $refs.subject.value = option.dataset.subject;
                    if (option.dataset.body) $refs.body.value = option.dataset.body;
                  ">
            <option value="">No template</option>
            @foreach($templates as $template)
              <option value="{{ $template->id }}"
                      data-subject="{{ $template->subject }}"
                      data-body="{{ $template->body }}"
                      @selected($selectedTemplate?->id === $template->id)>{{ $template->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div>
        <label for="to" class="{{ $label }}">To</label>
        <input id="to" name="to" type="text" required value="{{ old('to', $replyTo?->from_email ?? $draft?->to) }}"
               placeholder="name@example.com, second@example.com" class="{{ $input }}">
        <p class="mt-1 text-xs text-slate-500">Separate multiple addresses with commas.</p>
      </div>

      <div class="grid sm:grid-cols-2 gap-5">
        <div>
          <label for="cc" class="{{ $label }}">Cc <span class="font-normal text-slate-400">(optional)</span></label>
          <input id="cc" name="cc" type="text" value="{{ old('cc', $draft?->cc) }}" class="{{ $input }}">
        </div>
        <div>
          <label for="bcc" class="{{ $label }}">Bcc <span class="font-normal text-slate-400">(optional)</span></label>
          <input id="bcc" name="bcc" type="text" value="{{ old('bcc', $draft?->bcc) }}" class="{{ $input }}">
        </div>
      </div>

      <div>
        <label for="subject" class="{{ $label }}">Subject</label>
        <input id="subject" name="subject" x-ref="subject" type="text" required maxlength="255"
               value="{{ old('subject', $subjectDefault) }}" class="{{ $input }}">
      </div>

      <div>
        <label for="body" class="{{ $label }}">Message</label>
        <textarea id="body" name="body" x-ref="body" rows="14" required maxlength="20000"
                  class="{{ $input }} font-mono text-[13px]">{{ old('body', $draft?->body ?? $selectedTemplate?->body) }}</textarea>
        <p class="mt-1 text-xs text-slate-500">
          HTML is supported. Merge tags such as <code class="px-1 bg-slate-100 rounded">@{{name}}</code>,
          <code class="px-1 bg-slate-100 rounded">@{{first_name}}</code> and
          <code class="px-1 bg-slate-100 rounded">@{{email}}</code> are replaced for broadcasts.
        </p>
      </div>

      <div>
        <label for="attachments" class="{{ $label }}">Attachments <span class="font-normal text-slate-400">(optional, up to 5 files, 10 MB each)</span></label>
        <input id="attachments" name="attachments[]" type="file" multiple
               class="block w-full text-sm text-slate-600 border border-slate-200 rounded-lg file:mr-4 file:py-2.5 file:px-4 file:border-0 file:bg-slate-100 file:text-slate-700 file:font-medium hover:file:bg-slate-200">
        <p class="mt-1 text-xs text-slate-500">Total message size may be limited by your mail provider.</p>
        @error('attachments')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        @error('attachments.*')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
      </div>

      <div class="flex flex-wrap items-center gap-3 pt-1">
        <button type="submit" class="px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition {{ $focus }}">Send message</button>
        <a href="{{ route('admin.mail.sent') }}" class="px-5 py-2.5 border border-slate-200 rounded-lg font-medium hover:bg-slate-50 transition {{ $focus }}">Sent mail</a>
        @if($draft)
          <button type="button" id="discard-draft" class="px-4 py-2.5 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50 {{ $focus }}">Discard draft</button>
        @endif
        <span id="draft-status" class="text-xs text-slate-500" aria-live="polite">Draft saves automatically as you type.</span>
      </div>
    </form>

    <script>
      (function () {
        const form = document.getElementById('compose-form');
        if (!form) return;

        const status = document.getElementById('draft-status');
        const csrf = @json(csrf_token());
        let timer;

        const value = (name) => form.elements[name]?.value ?? '';

        const save = () => {
          const payload = {
            email_account_id: value('email_account_id') || null,
            to: value('to'),
            cc: value('cc'),
            bcc: value('bcc'),
            subject: value('subject'),
            body: value('body'),
            in_reply_to: value('in_reply_to'),
          };

          fetch(@json(route('admin.mail.drafts.save')), {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(payload),
          })
            .then((response) => response.ok ? response.json() : Promise.reject())
            .then(() => {
              if (status) status.textContent = 'Draft saved just now.';
            })
            .catch(() => {
              if (status) status.textContent = 'Draft could not be saved.';
            });
        };

        form.addEventListener('input', () => {
          clearTimeout(timer);
          timer = setTimeout(save, 2000);
        });

        form.addEventListener('submit', () => clearTimeout(timer));

        const discard = document.getElementById('discard-draft');
        if (discard) {
          discard.addEventListener('click', () => {
            fetch(@json(route('admin.mail.drafts.discard')), {
              method: 'DELETE',
              headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
              },
            }).then((response) => {
              if (!response.ok) return;

              ['to', 'cc', 'bcc', 'subject', 'body'].forEach((name) => {
                if (form.elements[name]) form.elements[name].value = '';
              });

              if (status) status.textContent = 'Draft discarded.';
            });
          });
        }
      })();
    </script>
  @endif
</div>
@endsection
