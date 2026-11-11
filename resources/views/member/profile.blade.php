@extends('layouts.app')
@section('content')
@php
  $fields = ['first_name','last_name','phone','professional_title','technology_category','experience_level','current_organization','education','bio','country','state','city','linkedin','github','portfolio','website'];
  $filled = 0; foreach ($fields as $f) if (!empty($profile->$f)) $filled++;
  $completion = (int) round(($filled / count($fields)) * 100);

  $labels = ['first_name'=>'First name','last_name'=>'Last name','phone'=>'Phone','professional_title'=>'Professional title','technology_category'=>'Technology category','experience_level'=>'Experience level','current_organization'=>'Current organization','education'=>'Education','bio'=>'Bio','country'=>'Country','state'=>'State','city'=>'City','linkedin'=>'LinkedIn','github'=>'GitHub','portfolio'=>'Portfolio','website'=>'Website','facebook'=>'Facebook','twitter'=>'Twitter (X)','tiktok'=>'TikTok'];
  $miss = [];
  foreach ($labels as $f => $l) if (empty($profile->$f)) $miss[] = $l;

  $inputClass = 'w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $smallInput = 'border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';

  // Which Work Experience / Project slots already have data (these start expanded)
  $workShow = [];
  $projectShow = [];
  for ($i = 0; $i < 3; $i++) {
    $we = $user->workExperiences->get($i);
    $workShow[] = filled(old('work_title.'.$i, $we?->title)) || filled(old('work_company.'.$i, $we?->company));
    $pr = $user->projects->get($i);
    $projectShow[] = filled(old('project_title.'.$i, $pr?->title)) || filled(old('project_url.'.$i, $pr?->url));
  }

  // Options for the Skills / Interests pickers
  $skillOptions    = \App\Models\Skill::orderBy('name')->get(['id','name']);
  $interestOptions = \App\Models\Interest::orderBy('name')->get(['id','name']);
@endphp

<style>[x-cloak]{display:none !important}</style>

<div class="max-w-7xl mx-auto px-4 py-8 grid md:grid-cols-3 gap-6">

  <!-- Left: preview -->
  <aside class="md:col-span-1 space-y-6">
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm text-center">
      @if($profile->avatar)
        <img src="{{ $profile->avatar }}" class="w-24 h-24 mx-auto rounded-full object-cover border-4 border-slate-100" alt="{{ $user->name }}">
      @else
        <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center text-3xl font-bold">{{ strtoupper(substr($user->name,0,1)) }}</div>
      @endif
      <h2 class="mt-4 text-lg font-bold text-[#101E33]">{{ $user->name }}</h2>
      <p class="text-sm text-slate-500">{{ $profile->professional_title ?? 'Member' }}</p>
      <p class="text-xs text-slate-400 mt-1">{{ $profile->city }}{{ $profile->city && $profile->state ? ', ' : '' }}{{ $profile->state }}</p>

      <div class="mt-4">
        <div class="text-xs text-slate-500 mb-1">Profile Completion</div>
        <div class="h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-[#FF5C3D]" style="width: {{ $completion }}%"></div></div>
        <div class="text-xs text-slate-400 mt-1">{{ $completion }}%</div>
      </div>

      @if(!empty($miss))
      <div class="mt-4 text-left">
        <div class="text-xs text-slate-500 mb-1">Missing:</div>
        <div class="flex flex-wrap gap-1">
          @foreach($miss as $m)
            <span class="px-2 py-0.5 rounded-full bg-[#FF5C3D]/10 text-[#FF5C3D] text-xs">{{ $m }}</span>
          @endforeach
        </div>
      </div>
      @endif

      <div class="mt-4">
        <span class="text-xs px-2 py-1 rounded-full {{ $profile->is_public ? 'bg-green-50 text-green-600' : 'bg-slate-100 text-slate-500' }}">{{ $profile->is_public ? 'Public' : 'Private' }}</span>
      </div>
      <a href="/members/{{ $profile->username ?? $user->id }}" class="mt-4 block w-full text-center px-4 py-2 border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 transition">View Public Profile</a>
    </div>

    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h3 class="font-semibold text-[#101E33] mb-2">Tips</h3>
      <ul class="text-xs text-slate-500 space-y-1">
        <li>• Add a profile photo to stand out.</li>
        <li>• Fill in your bio and skills.</li>
        <li>• Add social links to connect.</li>
        <li>• Set your profile to public to be discovered.</li>
      </ul>
    </div>
  </aside>

  <!-- Right: form -->
  <div class="md:col-span-2">
    <div class="bg-white rounded-2xl p-8 border border-slate-100 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33] mb-6">Edit Profile</h2>

      @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-600 text-sm">{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-600 text-sm">
          <ul class="list-disc list-inside">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6" x-data="locationPicker()">
        @csrf

        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Profile Photo</label>
          <input type="file" name="avatar" accept="image/*" class="{{ $inputClass }}">
        </div>

        <!-- Name -->
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">First Name</label>
            <input name="first_name" value="{{ old('first_name', $profile->first_name) }}" class="{{ $inputClass }}">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Last Name</label>
            <input name="last_name" value="{{ old('last_name', $profile->last_name) }}" class="{{ $inputClass }}">
          </div>
        </div>

        <!-- Location: Country → State → City -->
        <div class="grid md:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Country</label>
            <select name="country" x-model="country" @change="state=''; city=''" class="{{ $inputClass }}">
              <option value="">Select country</option>
              <template x-for="c in countries" :key="c">
                <option :value="c" x-text="c" :selected="c === country"></option>
              </template>
            </select>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">State</label>
            <template x-if="statesForCountry.length">
              <select name="state" x-model="state" @change="city=''" class="{{ $inputClass }}">
                <option value="">Select state</option>
                <template x-for="st in statesForCountry" :key="st">
                  <option :value="st" x-text="st" :selected="st === state"></option>
                </template>
              </select>
            </template>
            <template x-if="!statesForCountry.length">
              <input name="state" x-model="state" placeholder="State / Province" class="{{ $inputClass }}">
            </template>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">City</label>
            <template x-if="citiesForState.length">
              <select name="city" x-model="city" class="{{ $inputClass }}">
                <option value="">Select city</option>
                <template x-for="ci in citiesForState" :key="ci">
                  <option :value="ci" x-text="ci" :selected="ci === city"></option>
                </template>
              </select>
            </template>
            <template x-if="!citiesForState.length">
              <input name="city" x-model="city" placeholder="City" class="{{ $inputClass }}">
            </template>
          </div>
        </div>

        <!-- Phone: follows the selected country -->
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Phone</label>
          <div class="flex">
            <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-slate-200 bg-slate-50 text-sm text-slate-600 min-w-[4.5rem] justify-center"
                  x-text="dialCode || '+'"></span>
            <input type="tel" x-model="phoneLocal" inputmode="tel"
                   :placeholder="dialCode ? 'e.g. 8012345678' : 'Select a country first, or type +code number'"
                   class="w-full border border-slate-200 rounded-r-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          </div>
          <!-- Full international number is what gets saved -->
          <input type="hidden" name="phone" :value="fullPhone">
          <p class="text-xs text-slate-400 mt-1" x-show="dialCode">Don't type the leading 0. The country code is added automatically.</p>
        </div>

        <!-- Professional -->
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Professional Title</label>
            <input name="professional_title" value="{{ old('professional_title', $profile->professional_title) }}" placeholder="e.g. Full-Stack Developer" class="{{ $inputClass }}">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Technology Category</label>
            <input name="technology_category" value="{{ old('technology_category', $profile->technology_category) }}" placeholder="e.g. Web Development, Data Science" class="{{ $inputClass }}">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Experience Level</label>
            <select name="experience_level" class="{{ $inputClass }}">
              @foreach(['Student','Beginner','Intermediate','Experienced','Expert'] as $e)
                <option value="{{ $e }}" @selected(old('experience_level', $profile->experience_level)===$e)>{{ $e }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Current Organization</label>
            <input name="current_organization" value="{{ old('current_organization', $profile->current_organization) }}" class="{{ $inputClass }}">
          </div>
        </div>

        <!-- Education -->
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Education</label>
          <textarea name="education" rows="3"
            placeholder="Degree, field, institution and year. One entry per line. e.g.&#10;B.Sc. Computer Science, University of Lagos (2019)&#10;Google Data Analytics Certificate (2022)"
            class="{{ $inputClass }}">{{ old('education', $profile->education) }}</textarea>
          <p class="text-xs text-slate-400 mt-1">Include your highest qualification, school, graduation year, plus relevant certifications or bootcamps.</p>
        </div>

        <!-- Chapter -->
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">My Chapter</label>
          <select name="chapter_id" class="{{ $inputClass }}">
            <option value="">No chapter</option>
            @foreach($chapters as $ch)
              <option value="{{ $ch->id }}" @selected(old('chapter_id', $selectedChapter?->id)==$ch->id)>{{ $ch->name }}</option>
            @endforeach
          </select>
        </div>

        <!-- Bio -->
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Short Biography</label>
          <textarea name="bio" rows="4" class="{{ $inputClass }}">{{ old('bio', $profile->bio) }}</textarea>
        </div>

        <!-- Social links -->
        <div x-data="socialPicker()" class="space-y-3">
          <div class="flex items-center gap-2">
            <select x-model="selected" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
              <option value="">Choose a platform</option>
              <template x-for="p in available" :key="p.key">
                <option :value="p.key" x-text="p.label"></option>
              </template>
            </select>
            <button type="button" @click="add()" class="px-4 py-2 bg-[#101E33] text-white rounded-lg text-sm font-medium hover:opacity-90 transition">+ Add social media</button>
          </div>
          <template x-for="(item, idx) in items" :key="item.key">
            <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100">
              <div class="w-10 text-sm font-medium text-[#101E33]" x-text="labelOf(item.key)"></div>
              <input :name="item.key" x-model="item.value" placeholder="https://..." class="flex-1 border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
              <button type="button" @click="remove(idx)" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
            </div>
          </template>
          <!-- hidden inputs for existing values not in items? already included -->
        </div>

        <div class="flex items-center gap-2">
          <input type="checkbox" name="is_public" id="is_public" value="1" @checked(old('is_public', $profile->is_public)) class="rounded text-[#FF5C3D] focus:ring-[#FF5C3D]">
          <label for="is_public" class="text-sm text-slate-600">Make my profile public and discoverable</label>
        </div>

        <!-- Skills: chip picker -->
        <div x-data="tagPicker(@js($skillOptions), @js(old('skills', $user->skills->pluck('id')->toArray())), 'skills[]')"
             @click.outside="open = false" class="relative">
          <label class="block text-xs font-medium text-slate-500 mb-1">Skills</label>
          <div @click="$refs.input.focus(); open = true"
               class="min-h-[3rem] flex flex-wrap items-center gap-2 border border-slate-200 rounded-lg px-3 py-2 cursor-text focus-within:ring-2 focus-within:ring-[#FF5C3D]">
            <template x-for="o in chosen" :key="o.id">
              <span class="inline-flex items-center gap-1 pl-3 pr-1.5 py-1 rounded-full bg-[#FF5C3D]/10 text-[#FF5C3D] text-sm">
                <span x-text="o.name"></span>
                <button type="button" @click.stop="remove(o.id)" class="w-5 h-5 rounded-full hover:bg-[#FF5C3D]/20 leading-none" aria-label="Remove skill">&times;</button>
              </span>
            </template>
            <input x-ref="input" x-model="query" type="text" autocomplete="off"
                   @focus="open = true"
                   @keydown.enter.prevent="addFirst()"
                   @keydown.escape="open = false"
                   @keydown.backspace="if (!query && selected.length) selected.pop()"
                   placeholder="Search and add skills..."
                   class="flex-1 min-w-[8rem] bg-transparent outline-none text-sm py-1">
          </div>
          <div x-show="open" x-cloak class="absolute z-20 mt-1 w-full max-h-56 overflow-auto bg-white border border-slate-200 rounded-lg shadow-lg">
            <template x-for="o in available" :key="o.id">
              <button type="button" @click="add(o)" class="block w-full text-left px-4 py-2 text-sm hover:bg-slate-50" x-text="o.name"></button>
            </template>
            <p x-show="!available.length" class="px-4 py-2 text-sm text-slate-400">No matching skills</p>
          </div>
          <template x-for="o in chosen" :key="'h'+o.id"><input type="hidden" :name="name" :value="o.id"></template>
          <p class="text-xs text-slate-400 mt-1">Click the box, pick a skill from the list, then keep adding more.</p>
        </div>

        <!-- Work Experience (collapsed until you add one) -->
        <div x-data="entryList(@js($workShow))">
          <div class="flex items-center justify-between mb-2">
            <div>
              <h3 class="text-lg font-bold text-[#101E33]">Work Experience</h3>
              <p class="text-xs text-slate-500">Add up to 3 positions.</p>
            </div>
            <button type="button" @click="add()" x-show="canAdd" x-cloak
                    class="px-4 py-2 rounded-lg border border-[#FF5C3D] text-[#FF5C3D] text-sm font-medium hover:bg-[#FF5C3D] hover:text-white transition">+ Add Experience</button>
          </div>
          <p x-show="!anyShown" x-cloak class="text-sm text-slate-400 mb-2">No work experience added yet.</p>

          @for($i = 0; $i < 3; $i++)
            @php $we = $user->workExperiences->get($i); @endphp
            <div x-show="show[{{ $i }}]" x-cloak data-entry class="grid md:grid-cols-2 gap-3 mb-4 p-4 border border-slate-100 rounded-xl">
              <input name="work_title[]" value="{{ old('work_title.'.$i, $we?->title) }}" placeholder="Job title" class="{{ $smallInput }}">
              <input name="work_company[]" value="{{ old('work_company.'.$i, $we?->company) }}" placeholder="Company" class="{{ $smallInput }}">
              <input name="work_location[]" value="{{ old('work_location.'.$i, $we?->location) }}" placeholder="Location" class="{{ $smallInput }}">
              <div class="flex gap-2">
                <div class="flex-1">
                  <label class="block text-[11px] text-slate-400 mb-0.5">Start date</label>
                  <input type="date" name="work_start[]" value="{{ old('work_start.'.$i, $we?->start_date?->format('Y-m-d')) }}" class="w-full {{ $smallInput }}">
                </div>
                <div class="flex-1">
                  <label class="block text-[11px] text-slate-400 mb-0.5">End date</label>
                  <input type="date" name="work_end[]" value="{{ old('work_end.'.$i, $we?->end_date?->format('Y-m-d')) }}" class="w-full {{ $smallInput }}">
                </div>
              </div>
              <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="work_current[{{ $i }}]" value="1" @checked(old('work_current.'.$i, $we?->is_current))> Currently working here</label>
              <textarea name="work_desc[]" rows="2" placeholder="Description" class="md:col-span-2 {{ $smallInput }}">{{ old('work_desc.'.$i, $we?->description) }}</textarea>
              <div class="md:col-span-2 text-right">
                <button type="button" @click="remove({{ $i }}, $el.closest('[data-entry]'))" class="text-xs text-red-500 hover:underline">Remove</button>
              </div>
            </div>
          @endfor
        </div>

        <!-- Projects (collapsed until you add one) -->
        <div x-data="entryList(@js($projectShow))">
          <div class="flex items-center justify-between mb-2">
            <div>
              <h3 class="text-lg font-bold text-[#101E33]">Projects</h3>
              <p class="text-xs text-slate-500">Add up to 3 projects.</p>
            </div>
            <button type="button" @click="add()" x-show="canAdd" x-cloak
                    class="px-4 py-2 rounded-lg border border-[#FF5C3D] text-[#FF5C3D] text-sm font-medium hover:bg-[#FF5C3D] hover:text-white transition">+ Add Project</button>
          </div>
          <p x-show="!anyShown" x-cloak class="text-sm text-slate-400 mb-2">No projects added yet.</p>

          @for($i = 0; $i < 3; $i++)
            @php $pr = $user->projects->get($i); @endphp
            <div x-show="show[{{ $i }}]" x-cloak data-entry class="grid md:grid-cols-2 gap-3 mb-4 p-4 border border-slate-100 rounded-xl">
              <input name="project_title[]" value="{{ old('project_title.'.$i, $pr?->title) }}" placeholder="Project title" class="{{ $smallInput }}">
              <input name="project_url[]" value="{{ old('project_url.'.$i, $pr?->url) }}" placeholder="Project URL" class="{{ $smallInput }}">
              <input name="project_tech[]" value="{{ old('project_tech.'.$i, $pr?->technologies) }}" placeholder="Technologies (comma separated)" class="md:col-span-2 {{ $smallInput }}">
              <textarea name="project_desc[]" rows="2" placeholder="Description" class="md:col-span-2 {{ $smallInput }}">{{ old('project_desc.'.$i, $pr?->description) }}</textarea>
              <div class="md:col-span-2 text-right">
                <button type="button" @click="remove({{ $i }}, $el.closest('[data-entry]'))" class="text-xs text-red-500 hover:underline">Remove</button>
              </div>
            </div>
          @endfor
        </div>

        <!-- Interests: chip picker -->
        <div x-data="tagPicker(@js($interestOptions), @js(old('interests', $user->interests->pluck('id')->toArray())), 'interests[]')"
             @click.outside="open = false" class="relative">
          <label class="block text-xs font-medium text-slate-500 mb-1">Interests</label>
          <div @click="$refs.input.focus(); open = true"
               class="min-h-[3rem] flex flex-wrap items-center gap-2 border border-slate-200 rounded-lg px-3 py-2 cursor-text focus-within:ring-2 focus-within:ring-[#FF5C3D]">
            <template x-for="o in chosen" :key="o.id">
              <span class="inline-flex items-center gap-1 pl-3 pr-1.5 py-1 rounded-full bg-[#101E33]/10 text-[#101E33] text-sm">
                <span x-text="o.name"></span>
                <button type="button" @click.stop="remove(o.id)" class="w-5 h-5 rounded-full hover:bg-[#101E33]/20 leading-none" aria-label="Remove interest">&times;</button>
              </span>
            </template>
            <input x-ref="input" x-model="query" type="text" autocomplete="off"
                   @focus="open = true"
                   @keydown.enter.prevent="addFirst()"
                   @keydown.escape="open = false"
                   @keydown.backspace="if (!query && selected.length) selected.pop()"
                   placeholder="Search and add interests..."
                   class="flex-1 min-w-[8rem] bg-transparent outline-none text-sm py-1">
          </div>
          <div x-show="open" x-cloak class="absolute z-20 mt-1 w-full max-h-56 overflow-auto bg-white border border-slate-200 rounded-lg shadow-lg">
            <template x-for="o in available" :key="o.id">
              <button type="button" @click="add(o)" class="block w-full text-left px-4 py-2 text-sm hover:bg-slate-50" x-text="o.name"></button>
            </template>
            <p x-show="!available.length" class="px-4 py-2 text-sm text-slate-400">No matching interests</p>
          </div>
          <template x-for="o in chosen" :key="'h'+o.id"><input type="hidden" :name="name" :value="o.id"></template>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Likes / Hobbies</label>
          <textarea name="likes" rows="3" placeholder="e.g. Reading, football, open source, music..." class="{{ $inputClass }}">{{ old('likes', $profile->likes) }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
          <button type="submit" class="px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Save Profile</button>
          <a href="/members/{{ $profile->username ?? $user->id }}" class="px-6 py-3 border border-slate-200 rounded-lg font-medium hover:bg-slate-50 transition">View Public Profile</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Chip-style multi-select: click the box, pick from the list, keep adding.
function tagPicker(options, selected, name) {
  return {
    options: options,
    name: name,
    selected: (selected || []).map(String),
    query: '',
    open: false,
    get chosen() {
      return this.options.filter(o => this.selected.includes(String(o.id)));
    },
    get available() {
      const q = this.query.toLowerCase().trim();
      return this.options.filter(o =>
        !this.selected.includes(String(o.id)) && o.name.toLowerCase().includes(q)
      );
    },
    add(o) {
      this.selected.push(String(o.id));
      this.query = '';
      this.$refs.input.focus();
    },
    addFirst() {
      if (this.available.length) this.add(this.available[0]);
    },
    remove(id) {
      this.selected = this.selected.filter(s => s !== String(id));
    }
  };
}

// Collapsible repeatable entries (Work Experience, Projects), max 3.
function entryList(show) {
  return {
    show: show,
    get canAdd() { return this.show.includes(false); },
    get anyShown() { return this.show.includes(true); },
    add() {
      const n = this.show.indexOf(false);
      if (n > -1) this.show[n] = true;
    },
    remove(i, el) {
      if (el) {
        el.querySelectorAll('input:not([type=checkbox]), textarea').forEach(f => f.value = '');
        el.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = false);
      }
      this.show[i] = false;
    }
  };
}

function locationPicker() {
  const dialCodes = {
    'Nigeria': '+234', 'Ghana': '+233', 'Kenya': '+254', 'South Africa': '+27',
    'Egypt': '+20', 'Rwanda': '+250', 'Senegal': '+221', 'Ethiopia': '+251',
    'Tanzania': '+255', 'Uganda': '+256', 'Cameroon': '+237', 'Ivory Coast': '+225',
    'Morocco': '+212', 'Tunisia': '+216', 'Algeria': '+213'
  };

  const stateData = {
    'Nigeria': ['Abia','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa','Benue','Borno','Cross River','Delta','Ebonyi','Edo','Ekiti','Enugu','FCT (Abuja)','Gombe','Imo','Jigawa','Kaduna','Kano','Katsina','Kebbi','Kogi','Kwara','Lagos','Nasarawa','Niger','Ogun','Ondo','Osun','Oyo','Plateau','Rivers','Sokoto','Taraba','Yobe','Zamfara'],
    'Ghana': ['Greater Accra','Ashanti','Western','Eastern','Central','Northern','Volta','Upper East','Upper West','Brong-Ahafo'],
    'Kenya': ['Nairobi','Mombasa','Kisumu','Nakuru','Eldoret','Machakos','Kiambu','Nyeri'],
    'South Africa': ['Gauteng','Western Cape','KwaZulu-Natal','Eastern Cape','Free State','Limpopo','Mpumalanga','North West','Northern Cape'],
    'Egypt': ['Cairo','Giza','Alexandria','Qalyubia','Sharqia','Dakahlia','Beheira','Minya','Assiut','Sohag'],
    'Rwanda': ['Kigali','Eastern','Western','Northern','Southern'],
    'Senegal': ['Dakar','Thies','Saint-Louis','Diourbel','Kaolack','Ziguinchor'],
    'Ethiopia': ['Addis Ababa','Oromia','Amhara','Tigray','SNNPR','Somali','Afar'],
    'Tanzania': ['Dar es Salaam','Arusha','Mwanza','Dodoma','Mbeya','Morogoro','Tanga'],
    'Uganda': ['Central','Eastern','Northern','Western'],
    'Cameroon': ['Centre','Littoral','West','Northwest','Southwest','Adamawa','East','Far North','North','South'],
    'Morocco': ['Casablanca-Settat','Rabat-Sale-Kenitra','Marrakech-Safi','Fes-Meknes','Tangier-Tetouan-Al Hoceima','Oriental','Souss-Massa','Draa-Tafilalet','Beni Mellal-Khenifra','Guelmim-Oued Noun','Laayoune-Sakia El Hamra','Dakhla-Oued Ed-Dahab'],
    'Tunisia': ['Tunis','Ariana','Ben Arous','Manouba','Nabeul','Zaghouan','Bizerte','Beja','Jendouba','Kef','Siliana','Kairouan','Kasserine','Sidi Bouzid','Sfax','Mahdia','Monastir','Sousse','Gabes','Medenine','Tataouine','Gafsa','Tozeur','Kebili'],
    'Algeria': ['Algiers','Oran','Constantine','Annaba','Blida','Batna','Setif','Djelfa','Sidi Bel Abbes','Biskra','Tebessa','El Oued','Skikda','Tiaret','Bejaia','Tlemcen','Ouargla','Mostaganem','Bordj Bou Arreridj','Chlef'],
    'Ivory Coast': ['Abidjan','Yamoussoukro','Bouake','Daloa','San Pedro','Korhogo','Man','Gagnoa']
  };

  const nigeriaCities = {
    'Abia': ['Umuahia','Aba','Ohafia','Arochukwu','Bende','Isuikwuato'],
    'Adamawa': ['Yola','Mubi','Numan','Jimeta','Ganye','Michika'],
    'Akwa Ibom': ['Uyo','Eket','Ikot Ekpene','Oron','Abak','Ikot Abasi'],
    'Anambra': ['Awka','Onitsha','Nnewi','Ekwulobia','Aguata','Ihiala'],
    'Bauchi': ['Bauchi','Azare','Misau',"Jama'are",'Katagum','Tafawa Balewa'],
    'Bayelsa': ['Yenagoa','Brass','Ogbia','Sagbama','Nembe','Ekeremor'],
    'Benue': ['Makurdi','Gboko','Otukpo','Katsina-Ala','Vandeikya','Zaki Biam'],
    'Borno': ['Maiduguri','Biu','Bama','Dikwa','Monguno','Damaturu'],
    'Cross River': ['Calabar','Ikom','Ogoja','Obudu','Ugep','Akamkpa'],
    'Delta': ['Asaba','Warri','Sapele','Ughelli','Agbor','Ozoro','Effurun'],
    'Ebonyi': ['Abakaliki','Afikpo','Onueke','Ezza','Ikwo'],
    'Edo': ['Benin City','Auchi','Ekpoma','Uromi','Igarra','Igueben'],
    'Ekiti': ['Ado-Ekiti','Ikere','Oye','Ilawe','Efon','Aramoko'],
    'Enugu': ['Enugu','Nsukka','Awgu','Udi','Oji River','Agbani'],
    'FCT (Abuja)': ['Garki','Wuse','Maitama','Asokoro','Kubwa','Gwagwalada','Bwari','Kuje','Abaji'],
    'Gombe': ['Gombe','Kumo','Billiri','Kaltungo','Dukku','Bajoga'],
    'Imo': ['Owerri','Orlu','Okigwe','Oguta','Mbaise','Mbano'],
    'Jigawa': ['Dutse','Hadejia','Gumel','Birnin Kudu','Kazaure','Ringim'],
    'Kaduna': ['Kaduna','Zaria','Kafanchan','Kagoro','Saminaka','Birnin Gwari'],
    'Kano': ['Kano','Nassarawa','Gwale','Fagge','Dala','Tarauni','Kumbotso'],
    'Katsina': ['Katsina','Daura','Funtua','Malumfashi','Mani','Dutsin-Ma'],
    'Kebbi': ['Birnin Kebbi','Argungu','Yauri','Zuru','Jega','Kamba'],
    'Kogi': ['Lokoja','Okene','Kabba','Idah','Ankpa','Dekina','Anyigba'],
    'Kwara': ['Ilorin','Offa','Omu-Aran','Jebba','Patigi','Lafiagi'],
    'Lagos': ['Ikeja','Lekki','Victoria Island','Surulere','Yaba','Ajah','Ikorodu','Badagry','Epe','Ojo','Alimosho','Agege'],
    'Nasarawa': ['Lafia','Keffi','Akwanga','Nasarawa','Karu','Doma'],
    'Niger': ['Minna','Suleja','Kontagora','Bida','Lapai','New Bussa'],
    'Ogun': ['Abeokuta','Ijebu Ode','Sagamu','Ota','Ilaro','Ifo'],
    'Ondo': ['Akure','Ondo','Owo','Okitipupa','Ikare','Ore'],
    'Osun': ['Osogbo','Ile-Ife','Ilesa','Ede','Ikire','Iwo'],
    'Oyo': ['Ibadan','Ogbomosho','Oyo','Iseyin','Saki','Eruwa'],
    'Plateau': ['Jos','Bukuru','Pankshin','Shendam','Langtang','Barkin Ladi'],
    'Rivers': ['Port Harcourt','Obio-Akpor','Bonny','Eleme','Ikwerre','Oyigbo','Okrika','Degema','Ahoada','Omuma','Etche','Tai','Gokana','Khana','Ogba-Egbema-Ndoni','Abua-Odual','Ahoada East','Ahoada West','Akuku-Toru','Andoni','Asari-Toru','Emohua','Opobo-Nkoro'],
    'Sokoto': ['Sokoto','Tambuwal','Wurno','Illela','Gwadabawa','Binji'],
    'Taraba': ['Jalingo','Wukari','Bali','Sardauna','Takum','Gembu'],
    'Yobe': ['Damaturu','Potiskum','Gashua','Nguru','Geidam','Buni Yadi'],
    'Zamfara': ['Gusau','Kaura Namoda','Talata Mafara','Anka','Maru','Bungudu']
  };

  const initialCountry = @js(old('country', $profile->country) ?? '');
  const initialPhone   = @js(old('phone', $profile->phone) ?? '');

  // Strip the country code from a saved number so only the local part shows in the input
  function stripDial(phone, country) {
    const dial = dialCodes[country];
    phone = (phone || '').trim();
    if (dial && phone.startsWith(dial)) return phone.slice(dial.length).trim();
    return phone;
  }

  return {
    countries: ['Nigeria','Ghana','Kenya','South Africa','Egypt','Rwanda','Senegal','Ethiopia','Tanzania','Uganda','Cameroon','Ivory Coast','Morocco','Tunisia','Algeria','Other'],
    country: initialCountry,
    state: @js(old('state', $profile->state) ?? ''),
    city: @js(old('city', $profile->city) ?? ''),
    phoneLocal: stripDial(initialPhone, initialCountry),

    get dialCode() {
      return dialCodes[this.country] || '';
    },
    get fullPhone() {
      const raw = (this.phoneLocal || '').trim();
      if (!raw) return '';
      // User typed their own international number (or country is "Other")
      if (raw.startsWith('+') || !this.dialCode) return raw.replace(/[^\d+]/g, '');
      const digits = raw.replace(/\D/g, '').replace(/^0+/, '');
      return this.dialCode + digits;
    },
    get statesForCountry() {
      return stateData[this.country] || [];
    },
    get citiesForState() {
      if (this.country === 'Nigeria') return nigeriaCities[this.state] || [];
      return [];
    }
  };
}
</script>

<script>
function socialPicker() {
  const platforms = [
    {key:'linkedin', label:'LinkedIn'},
    {key:'github', label:'GitHub'},
    {key:'facebook', label:'Facebook'},
    {key:'twitter', label:'Twitter (X)'},
    {key:'tiktok', label:'TikTok'},
  ];
  const existing = {
    linkedin: @json(old('linkedin', $profile->linkedin)),
    github: @json(old('github', $profile->github)),
    facebook: @json(old('facebook', $profile->facebook)),
    twitter: @json(old('twitter', $profile->twitter)),
    tiktok: @json(old('tiktok', $profile->tiktok)),
  };
  const items = [];
  platforms.forEach(p => { if (existing[p.key]) items.push({key: p.key, value: existing[p.key]}); });
  return {
    platforms,
    selected: '',
    items,
    get available() { return platforms.filter(p => !this.items.find(i => i.key === p.key)); },
    labelOf(key) { return platforms.find(p => p.key === key)?.label || key; },
    add() {
      if (!this.selected) return;
      this.items.push({key: this.selected, value: ''});
      this.selected = '';
    },
    remove(idx) { this.items.splice(idx, 1); }
  }
}
</script>

@endsection