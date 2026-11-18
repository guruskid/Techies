@php
  $editing = $chapter->exists;
  $inputClass = 'w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $labelClass = 'block text-sm font-medium text-slate-700 mb-1';
@endphp

<form method="POST" action="{{ $editing ? route('admin.chapters.update', $chapter) : route('admin.chapters.store') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-6">
  @csrf
  @if($editing)
    @method('PATCH')
  @endif

  <div>
    <label for="name" class="{{ $labelClass }}">Chapter name</label>
    <input id="name" name="name" value="{{ old('name', $chapter->name) }}" required placeholder="e.g. MWTT Lagos" class="{{ $inputClass }}">
    <p class="mt-1 text-xs text-slate-400">Use a clear name that includes the state or region.</p>
    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
  </div>

  <div x-data='{
      country: "{{ old('country', $chapter->country ?? 'Nigeria') }}",
      state: "{{ old('state', $chapter->state) }}",
      usedStates: @json($usedStates ?? []),
      nigeriaStates: ["Abia","Adamawa","Akwa Ibom","Anambra","Bauchi","Bayelsa","Benue","Borno","Cross River","Delta","Ebonyi","Edo","Ekiti","Enugu","FCT (Abuja)","Gombe","Imo","Jigawa","Kaduna","Kano","Katsina","Kebbi","Kogi","Kwara","Lagos","Nasarawa","Niger","Ogun","Ondo","Osun","Oyo","Plateau","Rivers","Sokoto","Taraba","Yobe","Zamfara"],
      get isNigeria() { return this.country === "Nigeria"; }
    }' class="space-y-5">
    <div class="grid sm:grid-cols-2 gap-5">
      <div>
        <label for="country" class="{{ $labelClass }}">Country</label>
        <select id="country" name="country" x-model="country" @change="state = ''" class="{{ $inputClass }}">
          @foreach (['Nigeria','Ghana','Kenya','South Africa','Egypt','Rwanda','Senegal','Ethiopia','Tanzania','Uganda','Cameroon','Ivory Coast','Morocco','Tunisia','Algeria','Other'] as $countryOption)
            <option value="{{ $countryOption }}" @selected(old('country', $chapter->country ?? 'Nigeria') === $countryOption)>{{ $countryOption }}</option>
          @endforeach
        </select>
        @error('country')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
      </div>

      <div>
        <template x-if="isNigeria">
          <div>
            <label for="state" class="{{ $labelClass }}">State</label>
            <select id="state" name="state" x-model="state" class="{{ $inputClass }}">
              <option value="">Choose a state</option>
              <template x-for="st in nigeriaStates" :key="st">
                <option :value="st" :disabled="usedStates.includes(st) && st !== '{{ old('state', $chapter->state) }}'" x-text="st + (usedStates.includes(st) && st !== '{{ old('state', $chapter->state) }}' ? ' (already has a chapter)' : '')"></option>
              </template>
            </select>
            <p class="mt-1 text-xs text-slate-400">Each state can only have one chapter. Used states are disabled.</p>
            @error('state')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
          </div>
        </template>
        <template x-if="!isNigeria">
          <div>
            <label for="state" class="{{ $labelClass }}">State / Region</label>
            <input id="state" name="state" x-model="state" class="{{ $inputClass }}">
            @error('state')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
          </div>
        </template>
      </div>
    </div>
  </div>

  <div>
    <label for="status" class="{{ $labelClass }}">Status</label>
    <select id="status" name="status" required class="{{ $inputClass }}">
      <option value="active" @selected(old('status', $chapter->status ?? 'active') === 'active')>Active</option>
      <option value="inactive" @selected(old('status', $chapter->status ?? 'active') === 'inactive')>Inactive</option>
    </select>
    @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
  </div>

  @php
    $selectedCoordinator = ($coordinators ?? collect())->firstWhere('id', (int) old('coordinator_id', $chapter->coordinator_id));
    $selectedCoordinatorName = $selectedCoordinator ? $selectedCoordinator->name.' ('.$selectedCoordinator->email.')' : 'No coordinator';
  @endphp
  <div x-data='{
      open: false,
      query: "",
      selected: @json(old('coordinator_id', $chapter->coordinator_id)),
      selectedName: @json($selectedCoordinatorName),
      coordinators: @json(($coordinators ?? collect())->map(fn ($user) => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email])->values()),
      get filtered() {
        const q = this.query.toLowerCase();
        if (!q) return this.coordinators;
        return this.coordinators.filter((user) => user.name.toLowerCase().includes(q) || user.email.toLowerCase().includes(q));
      }
    }' class="relative">
    <label class="{{ $labelClass }}">Coordinator</label>
    <input type="hidden" name="coordinator_id" :value="selected">
    <button type="button" @click="open = !open" class="{{ $inputClass }} text-left flex items-center justify-between">
      <span x-text="selectedName"></span>
      <span class="text-slate-400">▾</span>
    </button>
    <div x-show="open" x-cloak @click.outside="open = false" class="absolute z-20 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
      <div class="p-2 border-b border-slate-100">
        <input type="text" x-model="query" placeholder="Search users by name or email..." class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
      </div>
      <ul class="max-h-60 overflow-auto py-1">
        <li @click="selected = ''; selectedName = 'No coordinator'; open = false; query = ''" class="px-3 py-2 text-sm cursor-pointer hover:bg-slate-50">No coordinator</li>
        <template x-for="user in filtered" :key="user.id">
          <li @click="selected = user.id; selectedName = user.name + ' (' + user.email + ')'; open = false; query = ''" class="px-3 py-2 text-sm cursor-pointer hover:bg-slate-50" x-text="user.name + ' (' + user.email + ')'"></li>
        </template>
        <li x-show="filtered.length === 0" class="px-3 py-2 text-sm text-slate-500">No users found.</li>
      </ul>
    </div>
    @error('coordinator_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
  </div>

  <div>
    <label for="description" class="{{ $labelClass }}">Description</label>
    <textarea id="description" name="description" rows="5" class="{{ $inputClass }}">{{ old('description', $chapter->description) }}</textarea>
    @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
  </div>

  <details class="rounded-lg border border-slate-200 p-4">
    <summary class="cursor-pointer text-sm font-medium text-slate-700">Advanced settings</summary>
    <div class="mt-4">
      <label for="slug" class="{{ $labelClass }}">Slug <span class="text-slate-400 font-normal">(optional)</span></label>
      <input id="slug" name="slug" value="{{ old('slug', $chapter->slug) }}" class="{{ $inputClass }}">
      <p class="mt-1 text-xs text-slate-400">Leave blank to generate from the chapter name.</p>
      @error('slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
  </details>

  <div class="flex items-center gap-3">
    <button class="px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">{{ $editing ? 'Update chapter' : 'Create chapter' }}</button>
    <a href="{{ route('admin.chapters.index') }}" class="px-5 py-2.5 text-slate-500 hover:text-slate-700">Cancel</a>
  </div>
</form>
