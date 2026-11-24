@php
  $editing = $announcement->exists;
  $inputClass = 'w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $labelClass = 'block text-sm font-medium text-slate-700 mb-1';
  $hintClass = 'mt-1 text-xs text-slate-500';
@endphp

<form method="POST"
      action="{{ $editing ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}"
      class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-6">
  @csrf
  @if($editing)
    @method('PATCH')
  @endif

  <div>
    <label for="title" class="{{ $labelClass }}">Title</label>
    <input id="title" name="title" required maxlength="200" value="{{ old('title', $announcement->title) }}" class="{{ $inputClass }}" placeholder="e.g. Chapter meetups resume this month">
    @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
  </div>

  <div>
    <label for="body" class="{{ $labelClass }}">Message</label>
    <textarea id="body" name="body" rows="8" required maxlength="50000" data-wysiwyg class="{{ $inputClass }}" placeholder="Write the announcement members should see.">{{ old('body', $announcement->body) }}</textarea>
    <p class="{{ $hintClass }}">Line breaks are preserved. Keep it short and actionable.</p>
    @error('body')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
  </div>

  <div>
    <label for="chapter_id" class="{{ $labelClass }}">Audience</label>
    <select id="chapter_id" name="chapter_id" class="{{ $inputClass }}">
      <option value="">All members</option>
      @foreach($chapters as $chapter)
        <option value="{{ $chapter->id }}" @selected((string) old('chapter_id', $announcement->chapter_id) === (string) $chapter->id)>{{ $chapter->name }}</option>
      @endforeach
    </select>
    <p class="{{ $hintClass }}">Chapter announcements are still shown publicly but are labelled with the chapter.</p>
    @error('chapter_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
  </div>

  <label class="flex items-start gap-3 text-sm text-slate-700">
    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $editing ? $announcement->is_published : true)) class="mt-0.5 rounded text-[#FF5C3D] focus:ring-[#FF5C3D]">
    <span>Publish immediately
      <span class="block text-xs text-slate-500">Uncheck to save a draft. Publishing sets the date shown on the news page.</span>
    </span>
  </label>

  <div class="flex items-center gap-3">
    <button type="submit" class="px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">{{ $editing ? 'Save announcement' : 'Create announcement' }}</button>
    <a href="{{ route('admin.announcements.index') }}" class="px-5 py-2.5 text-slate-500 hover:text-slate-700">Cancel</a>
  </div>
</form>

@include('partials.wysiwyg')
