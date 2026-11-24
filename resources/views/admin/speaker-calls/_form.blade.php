@php
  $editing = $speakerCall->exists;
  $inputClass = 'w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $labelClass = 'block text-sm font-medium text-slate-700 mb-1';
  $errorClass = 'mt-1 text-sm text-red-600';
@endphp

<form method="POST" action="{{ $editing ? route('admin.speaker-calls.update', $speakerCall) : route('admin.speaker-calls.store') }}" class="space-y-6 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
  @csrf
  @if($editing)
    @method('PATCH')
  @endif

  <div>
    <label for="title" class="{{ $labelClass }}">Call title</label>
    <input id="title" name="title" value="{{ old('title', $speakerCall->title) }}" required maxlength="200" class="{{ $inputClass }}" placeholder="e.g. Call for speakers: Lagos Frontend Meetup">
    @error('title')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
  </div>

  <div>
    <label for="description" class="{{ $labelClass }}">Description</label>
    <textarea id="description" name="description" rows="6" data-wysiwyg class="{{ $inputClass }}" placeholder="Explain the event, audience and what you are looking for.">{{ old('description', $speakerCall->description) }}</textarea>
    @error('description')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
  </div>

  <div>
    <label for="topics_text" class="{{ $labelClass }}">Topics speakers can choose from</label>
    <textarea id="topics_text" name="topics_text" rows="4" required class="{{ $inputClass }}" placeholder="Frontend performance&#10;Design systems&#10;Accessibility&#10;Career growth">{{ old('topics_text', $topicsText) }}</textarea>
    <p class="mt-1 text-xs text-slate-500">One topic per line. Speakers must pick one of these topics when they submit.</p>
    @error('topics_text')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
  </div>

  <div class="grid sm:grid-cols-2 gap-4">
    <div>
      <label for="opens_at" class="{{ $labelClass }}">Opens on</label>
      <input id="opens_at" type="date" name="opens_at" value="{{ old('opens_at', $speakerCall->opens_at?->toDateString()) }}" class="{{ $inputClass }}">
      @error('opens_at')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
    </div>
    <div>
      <label for="closes_at" class="{{ $labelClass }}">Closes on</label>
      <input id="closes_at" type="date" name="closes_at" value="{{ old('closes_at', $speakerCall->closes_at?->toDateString()) }}" class="{{ $inputClass }}">
      @error('closes_at')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
    </div>
  </div>

  <div>
    <label for="event_id" class="{{ $labelClass }}">Linked event (optional)</label>
    <select id="event_id" name="event_id" class="{{ $inputClass }}">
      <option value="">No linked event</option>
      @foreach($events as $event)
        <option value="{{ $event->id }}" @selected((string) old('event_id', $speakerCall->event_id) === (string) $event->id)>
          {{ $event->title }} — {{ $event->start_date?->format('M j, Y') }}
        </option>
      @endforeach
    </select>
    <p class="mt-1 text-xs text-slate-500">Linking an event shows the call on that event and keeps it in the right hub or chapter.</p>
    @error('event_id')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
  </div>

  <div>
    <label for="status" class="{{ $labelClass }}">Status</label>
    <select id="status" name="status" class="{{ $inputClass }}">
      @foreach(\App\Models\SpeakerCall::statuses() as $value => $label)
        <option value="{{ $value }}" @selected(old('status', $speakerCall->status ?? 'open') === $value)>{{ $label }}</option>
      @endforeach
    </select>
    <p class="mt-1 text-xs text-slate-500">Only open calls accept proposals. Drafts are hidden from the public.</p>
    @error('status')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
  </div>

  <div class="flex flex-wrap items-center gap-3">
    <button class="px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">{{ $editing ? 'Save changes' : 'Create call' }}</button>
    <a href="{{ route('admin.speaker-calls.index') }}" class="px-5 py-2.5 text-slate-500 hover:text-slate-700">Cancel</a>
  </div>
</form>

@include('partials.wysiwyg')
