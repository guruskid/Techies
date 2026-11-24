@php
  $editing = $opportunity->exists;
  $inputClass = 'w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $labelClass = 'block text-sm font-medium text-slate-700 mb-1';
  $isStaff = auth()->user()->isAdmin() || auth()->user()->isSuperAdmin();
  $currentStatus = old('status', $opportunity->status ?? 'draft');
@endphp

<form method="POST" action="{{ $editing ? route('admin.opportunities.update', $opportunity) : route('admin.opportunities.store') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-5">
  @csrf
  @if($editing)
    @method('PATCH')
  @endif

  <div class="grid sm:grid-cols-2 gap-5">
    <div>
      <label for="title" class="{{ $labelClass }}">Title</label>
      <input id="title" name="title" value="{{ old('title', $opportunity->title) }}" required class="{{ $inputClass }}">
      @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
      <label for="slug" class="{{ $labelClass }}">Slug <span class="text-slate-400 font-normal">(optional)</span></label>
      <input id="slug" name="slug" value="{{ old('slug', $opportunity->slug) }}" class="{{ $inputClass }}">
      @error('slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
  </div>

  <div class="grid sm:grid-cols-2 gap-5">
    <div>
      <label for="organization" class="{{ $labelClass }}">Organization</label>
      <input id="organization" name="organization" value="{{ old('organization', $opportunity->organization) }}" class="{{ $inputClass }}">
    </div>
    <div>
      <label for="category_id" class="{{ $labelClass }}">Category</label>
      <select id="category_id" name="category_id" class="{{ $inputClass }}">
        <option value="">No category</option>
        @foreach($categories as $category)
          <option value="{{ $category->id }}" @selected(old('category_id', $opportunity->category_id) == $category->id)>{{ $category->name }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="grid sm:grid-cols-2 gap-5">
    <div>
      <label for="location" class="{{ $labelClass }}">Location</label>
      <input id="location" name="location" value="{{ old('location', $opportunity->location) }}" class="{{ $inputClass }}">
    </div>
    <div>
      <label for="deadline" class="{{ $labelClass }}">Deadline</label>
      <input id="deadline" name="deadline" type="date" value="{{ old('deadline', optional($opportunity->deadline)->format('Y-m-d')) }}" class="{{ $inputClass }}">
    </div>
  </div>

  <div class="flex flex-wrap gap-6">
    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
      <input type="checkbox" name="is_remote" value="1" @checked(old('is_remote', $opportunity->is_remote)) class="rounded border-slate-300 text-[#FF5C3D] focus:ring-[#FF5C3D]">
      Remote
    </label>
    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
      <input type="checkbox" name="is_member_only" value="1" @checked(old('is_member_only', $opportunity->is_member_only)) class="rounded border-slate-300 text-[#FF5C3D] focus:ring-[#FF5C3D]">
      Members only (internal first)
    </label>
  </div>

  <div>
    <label for="application_link" class="{{ $labelClass }}">External application link</label>
    <input id="application_link" name="application_link" type="url" value="{{ old('application_link', $opportunity->application_link) }}" class="{{ $inputClass }}">
  </div>

  <div>
    <label for="cover_image" class="{{ $labelClass }}">Cover image URL</label>
    <input id="cover_image" name="cover_image" type="url" value="{{ old('cover_image', $opportunity->cover_image) }}" class="{{ $inputClass }}">
  </div>

  <div>
    <label for="eligibility" class="{{ $labelClass }}">Eligibility</label>
    <textarea id="eligibility" name="eligibility" rows="3" data-wysiwyg class="{{ $inputClass }}">{{ old('eligibility', $opportunity->eligibility) }}</textarea>
  </div>

  <div>
    <label for="description" class="{{ $labelClass }}">Description</label>
    <textarea id="description" name="description" rows="6" data-wysiwyg class="{{ $inputClass }}">{{ old('description', $opportunity->description) }}</textarea>
  </div>

  @if($isStaff)
    <div>
      <label for="status" class="{{ $labelClass }}">Status</label>
      <select id="status" name="status" required class="{{ $inputClass }}">
        <option value="draft" @selected($currentStatus === 'draft')>Draft</option>
        <option value="published" @selected($currentStatus === 'published')>Published</option>
      </select>
      <p class="mt-1 text-xs text-slate-400">Publishing notifies all active members by email.</p>
    </div>
  @else
    <div class="rounded-xl bg-slate-50 border border-slate-200 p-5">
      <p class="text-sm text-slate-600">
        <strong class="text-[#101E33]">Approval:</strong> your opportunity is saved as a draft.
        An admin reviews it and publishes it for the community — you do not need to choose a status.
      </p>
      <input type="hidden" name="status" value="{{ $currentStatus }}">
    </div>
  @endif

  @if($editing && $opportunity->applications->count())
    <div class="rounded-lg border border-slate-200 p-4">
      <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
        <h3 class="font-semibold text-[#101E33]">Applicants ({{ $opportunity->applications->count() }})</h3>
        <a href="{{ route('admin.opportunities.applications.export', $opportunity) }}"
           class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold border border-[#101E33] bg-[#101E33] text-white hover:opacity-90 transition">
          Export CSV<span class="sr-only"> of applicants for {{ $opportunity->title }}</span>
        </a>
      </div>
      <p class="mb-3 text-xs text-slate-500">Changing a status emails the applicant immediately.</p>
      <ul class="space-y-3 text-sm">
        @foreach($opportunity->applications as $application)
          <li class="border-t border-slate-100 pt-3 first:border-0 first:pt-0">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div class="min-w-0">
                <div class="font-medium text-[#101E33]">{{ $application->user?->name ?? 'Deleted member' }}</div>
                <div class="text-xs text-slate-500 break-all">
                  {{ $application->user?->email }}
                  @if($application->applied_at) · Applied {{ $application->applied_at->format('M j, Y') }} @endif
                </div>
              </div>
              @if($application->user)
                <form method="POST" action="{{ route('admin.opportunities.applications.update', [$opportunity, $application]) }}" class="flex items-center gap-2">
                  @csrf
                  <label for="application-status-{{ $application->id }}" class="sr-only">Status for {{ $application->user->name }}</label>
                  <select id="application-status-{{ $application->id }}" name="status" class="border border-slate-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
                    @foreach(\App\Models\OpportunityApplication::statuses() as $status)
                      <option value="{{ $status }}" @selected($application->status === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                  </select>
                  <button class="px-3 py-1.5 text-sm bg-[#101E33] text-white rounded-lg hover:opacity-90 transition">Update</button>
                </form>
              @else
                <span class="text-xs text-slate-500">{{ ucfirst($application->status) }}</span>
              @endif
            </div>
            @if($application->cover_letter)
              <p class="mt-2 text-xs text-slate-600 whitespace-pre-line">{{ $application->cover_letter }}</p>
            @endif
          </li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="flex items-center gap-3">
    <button class="px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">{{ $editing ? 'Update opportunity' : 'Create opportunity' }}</button>
    <a href="{{ route('admin.opportunities.index') }}" class="px-5 py-2.5 text-slate-500 hover:text-slate-700">Cancel</a>
  </div>
</form>

@include('partials.wysiwyg')
