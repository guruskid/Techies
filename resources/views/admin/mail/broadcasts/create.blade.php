@extends('layouts.admin')
@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $input = 'w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $label = 'block text-sm font-medium text-slate-700 mb-1';
  $audiences = \App\Models\EmailBroadcast::audiences();
@endphp

<div class="max-w-4xl mx-auto px-4 py-8 pb-12">
  <div class="mb-2">
    <a href="{{ route('admin.mail.broadcasts.index') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }}">Back to broadcasts</a>
    <h1 class="mt-2 text-2xl font-bold text-[#101E33]">New broadcast</h1>
    <p class="mt-1 text-sm text-slate-500">Choose an audience, write the message, review the recipient count, then send.</p>
  </div>

  @include('admin.mail._nav')

  @if($accounts->isEmpty())
    <div class="rounded-2xl border border-[#FF5C3D]/30 bg-[#FF5C3D]/5 p-6 text-sm text-slate-700">
      <p class="font-semibold text-[#101E33]">Connect a mailbox before creating a broadcast.</p>
      <a href="{{ route('admin.mail.accounts.create') }}" class="mt-3 inline-block px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 {{ $focus }}">Connect a mailbox</a>
    </div>
  @else
    @if($errors->any())
      <div class="mb-5 rounded-xl bg-red-50 border border-red-100 p-4 text-sm text-red-700">
        <ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
      </div>
    @endif

    <form method="POST" action="{{ route('admin.mail.broadcasts.store') }}"
          class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-5"
          x-data="{ audience: '{{ old('audience', 'all_members') }}' }">
      @csrf

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label for="name" class="{{ $label }}">Internal name</label>
          <input id="name" name="name" required maxlength="120" value="{{ old('name') }}" placeholder="November community update" class="{{ $input }}">
        </div>
        <div>
          <label for="email_account_id" class="{{ $label }}">Send from</label>
          <select id="email_account_id" name="email_account_id" required class="{{ $input }}">
            @foreach($accounts as $account)
              <option value="{{ $account->id }}" @selected((int) old('email_account_id', $defaultAccount?->id) === $account->id)>{{ $account->name }} — {{ $account->email }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label for="audience" class="{{ $label }}">Audience</label>
          <select id="audience" name="audience" x-model="audience" class="{{ $input }}">
            @foreach($audiences as $value => $audienceLabel)
              <option value="{{ $value }}">{{ $audienceLabel }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label for="template" class="{{ $label }}">Start from a template</label>
          <select id="template" name="email_template_id" class="{{ $input }}"
                  @change="
                    const option = $event.target.selectedOptions[0];
                    if (option.dataset.subject) $refs.subject.value = option.dataset.subject;
                    if (option.dataset.body) $refs.body.value = option.dataset.body;
                  ">
            <option value="">No template</option>
            @foreach($templates as $template)
              <option value="{{ $template->id }}"
                      data-subject="{{ $template->subject }}"
                      data-body="{{ $template->body }}">{{ $template->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="rounded-xl bg-slate-50 border border-slate-100 p-3 text-xs text-slate-600">
        <span x-show="audience === 'all_members'">This will email every active member ({{ $counts['all_members'] }}).</span>
        <span x-show="audience === 'newsletter'">This will email every newsletter subscriber ({{ $counts['newsletter'] }}).</span>
        <span x-show="audience === 'hub_owners'">This will email every active hub owner ({{ $counts['hub_owners'] }}).</span>
        <span x-show="audience === 'chapter'">Recipients are snapshotted from the selected chapter when you continue.</span>
        <span x-show="audience === 'event'">Recipients are the confirmed attendees of the selected event.</span>
        <span x-show="audience === 'opportunity_applicants'">Recipients are everyone who applied to the selected opportunity.</span>
      </div>

      <div x-show="audience === 'chapter'" x-cloak>
        <label for="audience_chapter" class="{{ $label }}">Chapter</label>
        <select id="audience_chapter" name="audience_id" :disabled="audience !== 'chapter'" class="{{ $input }}">
          <option value="">Choose a chapter</option>
          @foreach($chapters as $chapter)
            <option value="{{ $chapter->id }}" @selected((int) old('audience_id') === $chapter->id)>{{ $chapter->name }}</option>
          @endforeach
        </select>
      </div>

      <div x-show="audience === 'event'" x-cloak>
        <label for="audience_event" class="{{ $label }}">Event</label>
        <select id="audience_event" name="audience_id" :disabled="audience !== 'event'" class="{{ $input }}">
          <option value="">Choose an event</option>
          @foreach($events as $event)
            <option value="{{ $event->id }}" @selected((int) old('audience_id') === $event->id)>{{ $event->title }} — {{ $event->start_date?->format('M j, Y') }}</option>
          @endforeach
        </select>
      </div>

      <div x-show="audience === 'opportunity_applicants'" x-cloak>
        <label for="audience_opportunity" class="{{ $label }}">Opportunity</label>
        <select id="audience_opportunity" name="audience_id" :disabled="audience !== 'opportunity_applicants'" class="{{ $input }}">
          <option value="">Choose an opportunity</option>
          @foreach($opportunities as $opportunity)
            <option value="{{ $opportunity->id }}" @selected((int) old('audience_id') === $opportunity->id)>{{ $opportunity->title }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label for="subject" class="{{ $label }}">Subject</label>
        <input id="subject" name="subject" x-ref="subject" required maxlength="255" value="{{ old('subject') }}" class="{{ $input }}">
      </div>

      <div>
        <label for="body" class="{{ $label }}">Body</label>
        <textarea id="body" name="body" x-ref="body" rows="14" required maxlength="50000" class="{{ $input }} font-mono text-[13px]">{{ old('body') }}</textarea>
        <p class="mt-1 text-xs text-slate-500">
          HTML is supported. Per-recipient merge tags:
          <code class="px-1 bg-slate-100 rounded">@{{name}}</code>,
          <code class="px-1 bg-slate-100 rounded">@{{first_name}}</code>,
          <code class="px-1 bg-slate-100 rounded">@{{email}}</code>,
          <code class="px-1 bg-slate-100 rounded">@{{site_name}}</code>.
        </p>
      </div>

      <div>
        <label for="scheduled_at" class="{{ $label }}">Schedule for later <span class="font-normal text-slate-400">(optional)</span></label>
        <input id="scheduled_at" name="scheduled_at" type="datetime-local" value="{{ old('scheduled_at') }}" class="{{ $input }}">
        <p class="mt-1 text-xs text-slate-500">Leave blank to review the prepared draft first. A scheduled broadcast sends automatically at this time.</p>
        @error('scheduled_at')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
      </div>

      <div class="flex flex-wrap gap-3 pt-1">
        <button type="submit" class="px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition {{ $focus }}">Prepare broadcast</button>
        <a href="{{ route('admin.mail.broadcasts.index') }}" class="px-5 py-2.5 border border-slate-200 rounded-lg font-medium hover:bg-slate-50 transition {{ $focus }}">Cancel</a>
      </div>
    </form>
  @endif
</div>
@endsection
