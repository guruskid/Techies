@extends('layouts.app')
@section('content')
@include('partials.member-nav')
@php
  $completion = $profile->completionPercentage();
  $miss = $profile->missingCompletionFields();

  $inputClass = 'w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $smallInput = 'border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $labelClass = 'block text-sm font-medium text-slate-700 mb-1';
  $hintClass  = 'text-xs text-slate-500 mt-1';
  $addBtn     = 'px-4 py-2 rounded-lg border border-[#FF5C3D] text-[#FF5C3D] text-sm font-medium hover:bg-[#FF5C3D] hover:text-white transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';

  // Inline error message under a field
  $err = fn ($name) => $errors->has($name) ? '<p class="mt-1 text-xs text-red-600">'.e($errors->first($name)).'</p>' : '';

  // Which Work Experience / Project slots already have data (these start expanded)
  $workShow = [];
  $projectShow = [];
  for ($i = 0; $i < 3; $i++) {
    $we = $user->workExperiences->get($i);
    $workShow[] = filled(old('work_title.'.$i, $we?->title)) || filled(old('work_company.'.$i, $we?->company));
    $pr = $user->projects->get($i);
    $projectShow[] = filled(old('project_title.'.$i, $pr?->title)) || filled(old('project_url.'.$i, $pr?->url));
  }

  // Skills and interests share one chip picker, rendered in a loop below
  $pickers = [
    [
      'label' => 'Skills',
      'name' => 'skills[]',
      'options' => \App\Models\Skill::orderBy('name')->get(['id','name']),
      'selected' => old('skills', $user->skills->pluck('id')->toArray()),
      'chip' => 'bg-[#FF5C3D]/10 text-[#FF5C3D]',
      'chipHover' => 'hover:bg-[#FF5C3D]/20',
      'noun' => 'skill',
      'hint' => 'Click the box, pick from the list, then keep adding.',
    ],
    [
      'label' => 'Interests',
      'name' => 'interests[]',
      'options' => \App\Models\Interest::orderBy('name')->get(['id','name']),
      'selected' => old('interests', $user->interests->pluck('id')->toArray()),
      'chip' => 'bg-[#101E33]/10 text-[#101E33]',
      'chipHover' => 'hover:bg-[#101E33]/20',
      'noun' => 'interest',
      'hint' => null,
    ],
  ];

  // Where "Back to dashboard" goes. Change 'dashboard' to your member dashboard route name if different.
  $dashboardUrl = \Illuminate\Support\Facades\Route::has('dashboard') ? route('dashboard') : route('admin.dashboard');

  $profileScheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https';
  $profilePort = parse_url(config('app.url'), PHP_URL_PORT);
  $profileHost = config('techies.profile_domain')
      .($profilePort && ! in_array($profilePort, [80, 443], true) ? ':'.$profilePort : '');
@endphp

<style>[x-cloak]{display:none !important}</style>

<div class="max-w-7xl mx-auto px-4 py-8 grid md:grid-cols-3 gap-6 items-start">

  {{-- Preview: sits after the form on mobile, beside it on desktop --}}
  <aside class="order-last md:order-first md:col-span-1 md:sticky md:top-6 space-y-6" aria-label="Profile preview">
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm text-center">
      @if($profile->avatar)
        <img src="{{ $profile->avatar }}" class="w-24 h-24 mx-auto rounded-full object-cover border-4 border-slate-100" alt="">
      @else
        <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center text-3xl font-bold" aria-hidden="true">{{ strtoupper(substr($user->name,0,1)) }}</div>
      @endif
      <h2 class="mt-4 text-lg font-bold text-[#101E33]">{{ $user->name }}</h2>
      <p class="text-sm text-slate-500">{{ $profile->professional_title ?? 'Member' }}</p>
      <p class="text-xs text-slate-500 mt-1">{{ $profile->city }}{{ $profile->city && $profile->state ? ', ' : '' }}{{ $profile->state }}</p>

      <div class="mt-5 text-left">
        <div class="flex items-center justify-between text-xs text-slate-600 mb-1">
          <span>Profile completion</span>
          <span class="font-medium">{{ $completion }}%</span>
        </div>
        <div class="h-2 bg-slate-100 rounded-full overflow-hidden" role="progressbar" aria-valuenow="{{ $completion }}" aria-valuemin="0" aria-valuemax="100" aria-label="Profile completion">
          <div class="h-full bg-[#FF5C3D]" style="width: {{ $completion }}%"></div>
        </div>
      </div>

      @if(!empty($miss))
        <div class="mt-4 text-left">
          <div class="text-xs text-slate-600 mb-1">Still to add</div>
          <div class="flex flex-wrap gap-1">
            @foreach($miss as $m)
              <span class="px-2 py-0.5 rounded-full bg-[#FF5C3D]/10 text-[#FF5C3D] text-xs">{{ $m }}</span>
            @endforeach
          </div>
        </div>
      @endif

      <div class="mt-5 flex items-center justify-between gap-2">
        <span class="text-xs px-2 py-1 rounded-full {{ $profile->is_public ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }}">{{ $profile->is_public ? 'Public' : 'Private' }}</span>
        @if($profile->username)
          <a href="{{ $profile->publicUrl() }}" class="text-sm font-medium text-[#FF5C3D] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded">View public profile</a>
        @endif
      </div>
    </div>
  </aside>

  {{-- Form --}}
  <div class="md:col-span-2">
    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-100 shadow-sm">
      <a href="{{ $dashboardUrl }}"
         class="inline-block mb-3 text-sm text-[#FF5C3D] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded">
        Back to dashboard
      </a>
      <h1 class="text-xl font-bold text-[#101E33] mb-6">Edit profile</h1>

      @if($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-red-50 text-red-700 text-sm" role="alert">
          <p class="font-medium mb-1">Fix {{ $errors->count() }} {{ \Illuminate\Support\Str::plural('problem', $errors->count()) }} and save again:</p>
          <ul class="list-disc list-inside">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-10" x-data="locationPicker()">
        @csrf

        {{-- 1. About you --}}
        <section class="space-y-5" aria-labelledby="sec-about">
          <h2 id="sec-about" class="text-lg font-bold text-[#101E33]">About you</h2>

          <div>
            <label for="avatar" class="{{ $labelClass }}">Profile photo</label>
            <input id="avatar" type="file" name="avatar" accept="image/*"
                   class="block w-full text-sm text-slate-600 border border-slate-200 rounded-lg file:mr-4 file:py-2.5 file:px-4 file:border-0 file:bg-slate-100 file:text-slate-700 file:font-medium hover:file:bg-slate-200">
            {!! $err('avatar') !!}
          </div>

          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label for="first_name" class="{{ $labelClass }}">First name</label>
              <input id="first_name" name="first_name" value="{{ old('first_name', $profile->first_name) }}" class="{{ $inputClass }}">
              {!! $err('first_name') !!}
            </div>
            <div>
              <label for="last_name" class="{{ $labelClass }}">Last name</label>
              <input id="last_name" name="last_name" value="{{ old('last_name', $profile->last_name) }}" class="{{ $inputClass }}">
              {!! $err('last_name') !!}
            </div>
          </div>

          <div class="max-w-sm">
            <label for="date_of_birth" class="{{ $labelClass }}">Date of birth</label>
            <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', $profile->date_of_birth?->toDateString()) }}" max="{{ now()->subDay()->toDateString() }}" min="1900-01-01" class="{{ $inputClass }}">
            <p class="{{ $hintClass }}">Used only to send you a birthday message. It is never shown on your public profile.</p>
            {!! $err('date_of_birth') !!}
          </div>

          {{-- Public profile address --}}
          <div x-data='{
              username: @json(old("username", $profile->username) ?? ""),
              current: @json($profile->username),
              state: "idle",
              message: "",
              check() {
                const value = this.username.trim().toLowerCase();
                this.username = value;
                if (! value) { this.state = "idle"; this.message = ""; return; }
                if (value === this.current) { this.state = "ok"; this.message = "This is your current username."; return; }
                this.state = "checking";
                this.message = "Checking availability...";
                fetch("{{ route('profile.username.check') }}?username=" + encodeURIComponent(value), { headers: { "Accept": "application/json" } })
                  .then((response) => response.json())
                  .then((data) => { this.state = data.available ? "ok" : "error"; this.message = data.message; })
                  .catch(() => { this.state = "idle"; this.message = ""; });
              }
            }'>
            <label for="username" class="{{ $labelClass }}">Public profile address</label>
            <div class="flex flex-wrap items-stretch">
              <span class="inline-flex items-center px-3 py-2.5 rounded-t-lg md:rounded-l-lg md:rounded-tr-none border border-slate-200 bg-slate-50 text-sm text-slate-600">{{ $profileScheme }}://</span>
              <input id="username" name="username" x-model="username" @input.debounce.500ms="check()" @blur="check()"
                     value="{{ old('username', $profile->username) }}" placeholder="yourname" minlength="3" maxlength="30"
                     pattern="[a-z0-9](?:[a-z0-9-]*[a-z0-9])?" autocomplete="off" spellcheck="false" aria-describedby="username-status"
                     class="flex-1 min-w-[8rem] border border-slate-200 md:border-x-0 rounded-none px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
              <span class="inline-flex items-center px-3 py-2.5 rounded-b-lg md:rounded-r-lg md:rounded-bl-none border border-slate-200 bg-slate-50 text-sm text-slate-600">.{{ $profileHost }}</span>
            </div>
            <p id="username-status" class="text-xs mt-1 flex items-center gap-1" aria-live="polite"
               :class="{ 'text-red-600': state === 'error', 'text-green-700': state === 'ok', 'text-slate-500': state === 'idle' || state === 'checking' }">
              <span x-show="state === 'ok'" x-cloak class="font-bold">&#10003;</span>
              <span x-show="state === 'error'" x-cloak class="font-bold">&#10005;</span>
              <span x-text="message || '3 to 30 lowercase letters, numbers or hyphens.'"></span>
            </p>
            <p class="{{ $hintClass }}">Your profile will be at <span class="font-medium text-[#101E33]" x-text="'{{ $profileScheme }}://' + (username || 'yourname') + '.{{ $profileHost }}'"></span></p>
            {!! $err('username') !!}
          </div>

          <div>
            <label for="headline" class="{{ $labelClass }}">Headline</label>
            <input id="headline" name="headline" value="{{ old('headline', $profile->headline) }}" placeholder="e.g. Building products that connect African tech communities" class="{{ $inputClass }}">
            {!! $err('headline') !!}
          </div>

          <div>
            <label for="bio" class="{{ $labelClass }}">Short biography</label>
            <textarea id="bio" name="bio" rows="4" class="{{ $inputClass }}">{{ old('bio', $profile->bio) }}</textarea>
            {!! $err('bio') !!}
          </div>
        </section>

        {{-- 2. Location and contact --}}
        <section class="space-y-5" aria-labelledby="sec-location">
          <h2 id="sec-location" class="text-lg font-bold text-[#101E33]">Location and contact</h2>

          <div class="grid md:grid-cols-3 gap-4">
            <div>
              <label for="country" class="{{ $labelClass }}">Country</label>
              <select id="country" name="country" x-model="country" @change="state=''; city=''" class="{{ $inputClass }}">
                <option value="">Select country</option>
                <template x-for="c in countries" :key="c">
                  <option :value="c" x-text="c" :selected="c === country"></option>
                </template>
              </select>
            </div>
            <div>
              <label for="state" class="{{ $labelClass }}">State</label>
              <template x-if="statesForCountry.length">
                <select id="state" name="state" x-model="state" @change="city=''" class="{{ $inputClass }}">
                  <option value="">Select state</option>
                  <template x-for="st in statesForCountry" :key="st">
                    <option :value="st" x-text="st" :selected="st === state"></option>
                  </template>
                </select>
              </template>
              <template x-if="!statesForCountry.length">
                <input id="state" name="state" x-model="state" placeholder="State or province" class="{{ $inputClass }}">
              </template>
            </div>
            <div>
              <label for="city" class="{{ $labelClass }}">City</label>
              <template x-if="citiesForState.length">
                <select id="city" name="city" x-model="city" class="{{ $inputClass }}">
                  <option value="">Select city</option>
                  <template x-for="ci in citiesForState" :key="ci">
                    <option :value="ci" x-text="ci" :selected="ci === city"></option>
                  </template>
                </select>
              </template>
              <template x-if="!citiesForState.length">
                <input id="city" name="city" x-model="city" placeholder="City" class="{{ $inputClass }}">
              </template>
            </div>
          </div>

          <div>
            <label for="phone_local" class="{{ $labelClass }}">Phone</label>
            <div class="flex">
              <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-slate-200 bg-slate-50 text-sm text-slate-600 min-w-[4.5rem] justify-center" x-text="dialCode || '+'"></span>
              <input id="phone_local" type="tel" x-model="phoneLocal" inputmode="tel"
                     :placeholder="dialCode ? 'e.g. 8012345678' : 'Select a country first, or type +code number'"
                     class="w-full border border-slate-200 rounded-r-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
            </div>
            <input type="hidden" name="phone" :value="fullPhone">
            <p class="{{ $hintClass }}" x-show="dialCode">Leave out the leading 0. The country code is added for you.</p>
            {!! $err('phone') !!}
          </div>
        </section>

        {{-- 3. Professional --}}
        <section class="space-y-5" aria-labelledby="sec-pro">
          <h2 id="sec-pro" class="text-lg font-bold text-[#101E33]">Professional details</h2>

          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label for="professional_title" class="{{ $labelClass }}">Professional title</label>
              <input id="professional_title" name="professional_title" value="{{ old('professional_title', $profile->professional_title) }}" placeholder="e.g. Full-stack developer" class="{{ $inputClass }}">
              {!! $err('professional_title') !!}
            </div>
            <div>
              <label for="technology_category" class="{{ $labelClass }}">Technology category</label>
              <input id="technology_category" name="technology_category" value="{{ old('technology_category', $profile->technology_category) }}" placeholder="e.g. Web development, data science" class="{{ $inputClass }}">
              {!! $err('technology_category') !!}
            </div>
            <div>
              <label for="experience_level" class="{{ $labelClass }}">Experience level</label>
              <select id="experience_level" name="experience_level" class="{{ $inputClass }}">
                <option value="">Select level</option>
                @foreach(['Student','Beginner','Intermediate','Experienced','Expert'] as $e)
                  <option value="{{ $e }}" @selected(old('experience_level', $profile->experience_level)===$e)>{{ $e }}</option>
                @endforeach
              </select>
              {!! $err('experience_level') !!}
            </div>
            <div>
              <label for="current_organization" class="{{ $labelClass }}">Current organization</label>
              <input id="current_organization" name="current_organization" value="{{ old('current_organization', $profile->current_organization) }}" class="{{ $inputClass }}">
            </div>
          </div>

          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label for="chapter_id" class="{{ $labelClass }}">Add me to a chapter</label>
              <select id="chapter_id" name="chapter_id" class="{{ $inputClass }}">
                <option value="">Leave my chapters unchanged</option>
                @foreach($chapters as $ch)
                  <option value="{{ $ch->id }}" @selected(old('chapter_id', $selectedChapter?->id)==$ch->id)>{{ $ch->name }}</option>
                @endforeach
              </select>
              <p class="{{ $hintClass }}">Saving adds you to the selected chapter. It never removes your other chapters — leave a chapter from its page.</p>
            </div>
            <div>
              <label for="availability" class="{{ $labelClass }}">Availability</label>
              <input id="availability" name="availability" value="{{ old('availability', $profile->availability) }}" placeholder="e.g. Open to speaking, mentoring, freelance" class="{{ $inputClass }}">
            </div>
          </div>
        </section>

        {{-- 4. Education and credentials --}}
        <section class="space-y-5" aria-labelledby="sec-edu">
          <h2 id="sec-edu" class="text-lg font-bold text-[#101E33]">Education and credentials</h2>

          <div>
            <label for="education" class="{{ $labelClass }}">Education</label>
            <textarea id="education" name="education" rows="3"
              placeholder="One entry per line, e.g.&#10;B.Sc. Computer Science, University of Lagos (2019)&#10;Google Data Analytics Certificate (2022)"
              class="{{ $inputClass }}">{{ old('education', $profile->education) }}</textarea>
            <p class="{{ $hintClass }}">Include your highest qualification, school and graduation year.</p>
          </div>

          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label for="certifications" class="{{ $labelClass }}">Certifications</label>
              <textarea id="certifications" name="certifications" rows="3" placeholder="One certification per line" class="{{ $inputClass }}">{{ old('certifications', $profile->certifications) }}</textarea>
            </div>
            <div>
              <label for="languages" class="{{ $labelClass }}">Languages</label>
              <textarea id="languages" name="languages" rows="3" placeholder="e.g. English (fluent), Yoruba (native)" class="{{ $inputClass }}">{{ old('languages', $profile->languages) }}</textarea>
            </div>
          </div>
        </section>

        {{-- 5. Skills and interests --}}
        <section class="space-y-5" aria-labelledby="sec-skills">
          <h2 id="sec-skills" class="text-lg font-bold text-[#101E33]">Skills and interests</h2>

          @foreach($pickers as $p)
            <div x-data="tagPicker(@js($p['options']), @js($p['selected']), '{{ $p['name'] }}')" @click.outside="open = false" class="relative">
              <label for="picker-{{ $p['noun'] }}" class="{{ $labelClass }}">{{ $p['label'] }}</label>
              <div @click="$refs.input.focus(); open = true"
                   class="min-h-[3rem] flex flex-wrap items-center gap-2 border border-slate-200 rounded-lg px-3 py-2 cursor-text focus-within:ring-2 focus-within:ring-[#FF5C3D]">
                <template x-for="o in chosen" :key="o.id">
                  <span class="inline-flex items-center gap-1 pl-3 pr-1.5 py-1 rounded-full {{ $p['chip'] }} text-sm">
                    <span x-text="o.name"></span>
                    <button type="button" @click.stop="remove(o.id)" class="w-5 h-5 rounded-full {{ $p['chipHover'] }} leading-none" :aria-label="'Remove ' + o.name">&times;</button>
                  </span>
                </template>
                <input id="picker-{{ $p['noun'] }}" x-ref="input" x-model="query" type="text" autocomplete="off"
                       @focus="open = true"
                       @keydown.enter.prevent="addFirst()"
                       @keydown.escape="open = false"
                       @keydown.backspace="if (!query && selected.length) selected.pop()"
                       placeholder="Search and add {{ $p['noun'] }}s..."
                       class="flex-1 min-w-[8rem] bg-transparent outline-none text-sm py-1">
              </div>
              <div x-show="open" x-cloak class="absolute z-20 mt-1 w-full max-h-56 overflow-auto bg-white border border-slate-200 rounded-lg shadow-lg">
                <template x-for="o in available" :key="o.id">
                  <button type="button" @click="add(o)" class="block w-full text-left px-4 py-2 text-sm hover:bg-slate-50 focus:bg-slate-50 focus:outline-none" x-text="o.name"></button>
                </template>
                <p x-show="!available.length" class="px-4 py-2 text-sm text-slate-500">No matching {{ $p['noun'] }}s</p>
              </div>
              <template x-for="o in chosen" :key="'h'+o.id"><input type="hidden" :name="name" :value="o.id"></template>
              @if($p['hint'])<p class="{{ $hintClass }}">{{ $p['hint'] }}</p>@endif
            </div>
          @endforeach

          <div>
            <label for="likes" class="{{ $labelClass }}">Likes and hobbies</label>
            <textarea id="likes" name="likes" rows="3" placeholder="e.g. Reading, football, open source, music" class="{{ $inputClass }}">{{ old('likes', $profile->likes) }}</textarea>
          </div>
        </section>

        {{-- 6. Work experience --}}
        <section aria-labelledby="sec-work" x-data="entryList(@js($workShow))">
          <div class="flex items-center justify-between gap-3 mb-3">
            <div>
              <h2 id="sec-work" class="text-lg font-bold text-[#101E33]">Work experience</h2>
              <p class="text-xs text-slate-500">Add up to 3 positions.</p>
            </div>
            <button type="button" @click="add()" x-show="canAdd" x-cloak class="{{ $addBtn }}">Add experience</button>
          </div>
          <p x-show="!anyShown" x-cloak class="text-sm text-slate-500 mb-2">No work experience added yet.</p>

          @for($i = 0; $i < 3; $i++)
            @php $we = $user->workExperiences->get($i); @endphp
            <div x-show="show[{{ $i }}]" x-cloak data-entry class="grid md:grid-cols-2 gap-3 mb-4 p-4 border border-slate-100 rounded-xl">
              <input name="work_title[]" aria-label="Job title" value="{{ old('work_title.'.$i, $we?->title) }}" placeholder="Job title" class="{{ $smallInput }}">
              <input name="work_company[]" aria-label="Company" value="{{ old('work_company.'.$i, $we?->company) }}" placeholder="Company" class="{{ $smallInput }}">
              <input name="work_location[]" aria-label="Location" value="{{ old('work_location.'.$i, $we?->location) }}" placeholder="Location" class="{{ $smallInput }}">
              <div class="flex gap-2">
                <div class="flex-1">
                  <label class="block text-xs text-slate-600 mb-0.5">Start date</label>
                  <input type="date" name="work_start[]" value="{{ old('work_start.'.$i, $we?->start_date?->format('Y-m-d')) }}" class="w-full {{ $smallInput }}">
                </div>
                <div class="flex-1">
                  <label class="block text-xs text-slate-600 mb-0.5">End date</label>
                  <input type="date" name="work_end[]" value="{{ old('work_end.'.$i, $we?->end_date?->format('Y-m-d')) }}" class="w-full {{ $smallInput }}">
                </div>
              </div>
              <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="work_current[{{ $i }}]" value="1" class="rounded text-[#FF5C3D] focus:ring-[#FF5C3D]" @checked(old('work_current.'.$i, $we?->is_current))> Currently working here</label>
              <textarea name="work_desc[]" aria-label="Description" rows="2" placeholder="Description" class="md:col-span-2 {{ $smallInput }}">{{ old('work_desc.'.$i, $we?->description) }}</textarea>
              <div class="md:col-span-2 text-right">
                <button type="button" @click="remove({{ $i }}, $el.closest('[data-entry]'))" class="text-sm text-red-600 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 rounded">Remove this position</button>
              </div>
            </div>
          @endfor
        </section>

        {{-- 7. Projects --}}
        <section aria-labelledby="sec-projects" x-data="entryList(@js($projectShow))">
          <div class="flex items-center justify-between gap-3 mb-3">
            <div>
              <h2 id="sec-projects" class="text-lg font-bold text-[#101E33]">Projects</h2>
              <p class="text-xs text-slate-500">Add up to 3 projects.</p>
            </div>
            <button type="button" @click="add()" x-show="canAdd" x-cloak class="{{ $addBtn }}">Add project</button>
          </div>
          <p x-show="!anyShown" x-cloak class="text-sm text-slate-500 mb-2">No projects added yet.</p>

          @for($i = 0; $i < 3; $i++)
            @php $pr = $user->projects->get($i); @endphp
            <div x-show="show[{{ $i }}]" x-cloak data-entry class="grid md:grid-cols-2 gap-3 mb-4 p-4 border border-slate-100 rounded-xl">
              <input name="project_title[]" aria-label="Project title" value="{{ old('project_title.'.$i, $pr?->title) }}" placeholder="Project title" class="{{ $smallInput }}">
              <input name="project_url[]" aria-label="Project URL" value="{{ old('project_url.'.$i, $pr?->url) }}" placeholder="Project URL" class="{{ $smallInput }}">
              <input name="project_tech[]" aria-label="Technologies" value="{{ old('project_tech.'.$i, $pr?->technologies) }}" placeholder="Technologies (comma separated)" class="md:col-span-2 {{ $smallInput }}">
              <textarea name="project_desc[]" aria-label="Description" rows="2" placeholder="Description" class="md:col-span-2 {{ $smallInput }}">{{ old('project_desc.'.$i, $pr?->description) }}</textarea>
              <div class="md:col-span-2 text-right">
                <button type="button" @click="remove({{ $i }}, $el.closest('[data-entry]'))" class="text-sm text-red-600 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 rounded">Remove this project</button>
              </div>
            </div>
          @endfor
        </section>

        {{-- 8. Social links --}}
        <section aria-labelledby="sec-social" x-data="socialPicker()" class="space-y-3">
          <h2 id="sec-social" class="text-lg font-bold text-[#101E33]">Social links</h2>
          <div class="flex flex-wrap items-center gap-2">
            <select x-model="selected" aria-label="Platform" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
              <option value="">Choose a platform</option>
              <template x-for="p in available" :key="p.key">
                <option :value="p.key" x-text="p.label"></option>
              </template>
            </select>
            <button type="button" @click="add()" class="px-4 py-2 bg-[#101E33] text-white rounded-lg text-sm font-medium hover:opacity-90 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2">Add social link</button>
          </div>
          <p x-show="!items.length" x-cloak class="text-sm text-slate-500">No social links added yet.</p>

          <template x-for="(item, idx) in items" :key="item.key">
            <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100">
              <label :for="'social-' + item.key" class="w-24 shrink-0 text-sm font-medium text-[#101E33]" x-text="labelOf(item.key)"></label>
              <input :id="'social-' + item.key" :name="item.key" x-model="item.value" placeholder="https://..." class="flex-1 min-w-0 border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
              <button type="button" @click="remove(idx)" class="text-sm text-red-600 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 rounded">Remove</button>
            </div>
          </template>
          {{-- Removed platforms are sent empty so the saved link is cleared --}}
          <template x-for="key in removed" :key="'r' + key"><input type="hidden" :name="key" value=""></template>
        </section>

        {{-- 8b. Connected accounts --}}
        <section id="connections" class="space-y-3 scroll-mt-24" aria-labelledby="sec-connections">
          <div class="flex flex-wrap items-end justify-between gap-2">
            <div>
              <h2 id="sec-connections" class="text-lg font-bold text-[#101E33]">Connected accounts</h2>
              <p class="text-sm text-slate-500">Optional. Link the accounts you use so your profile reflects your work.</p>
            </div>
          </div>
          <div class="grid sm:grid-cols-2 gap-3">
            @foreach($oauthProviders as $provider => $providerConfig)
              @php $account = $connectedAccounts[$provider] ?? null; @endphp
              <div class="rounded-xl border border-slate-200 p-4">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <p class="font-semibold text-[#101E33]">{{ $providerConfig['label'] }}</p>
                    @if($account)
                      <p class="mt-1 truncate text-sm text-slate-600">
                        {{ $account->username ? '@'.$account->username : ($account->email ?: $account->name ?: 'Connected') }}
                      </p>
                    @else
                      <p class="mt-1 text-sm text-slate-500">Not connected</p>
                    @endif
                  </div>
                  @if($account?->avatar)
                    <img src="{{ $account->avatar }}" alt="" class="h-9 w-9 rounded-full object-cover border border-slate-100">
                  @endif
                </div>

                <div class="mt-3">
                  @if(! ($oauthConfigured[$provider] ?? false))
                    <span class="text-xs text-slate-400">Not available yet</span>
                  @elseif($account)
                    <form method="POST" action="{{ route('connections.destroy', $provider) }}">
                      @csrf
                      @method('DELETE')
                      <button class="text-sm font-semibold text-red-600 hover:underline">Disconnect</button>
                    </form>
                  @else
                    <a href="{{ route('connections.redirect', $provider) }}" class="inline-flex rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Connect {{ $providerConfig['label'] }}</a>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        </section>

        {{-- 9. Visibility --}}
        <section class="space-y-2" aria-labelledby="sec-visibility">
          <h2 id="sec-visibility" class="text-lg font-bold text-[#101E33]">Visibility</h2>
          <label for="is_public" class="flex items-start gap-3 text-sm text-slate-700">
            <input type="checkbox" name="is_public" id="is_public" value="1" @checked(old('is_public', $profile->is_public)) class="mt-0.5 rounded text-[#FF5C3D] focus:ring-[#FF5C3D]">
            <span>Make my profile public and discoverable
              <span class="block text-xs text-slate-500">Turn this on so other members and visitors can find you.</span>
            </span>
          </label>
        </section>

        {{-- 9b. Availability signals --}}
        <section class="space-y-3" aria-labelledby="sec-availability">
          <h2 id="sec-availability" class="text-lg font-bold text-[#101E33]">Open to</h2>
          <p class="text-sm text-slate-500">These signals appear on your profile and in the members directory.</p>
          <div class="grid sm:grid-cols-2 gap-3">
            @foreach([
              ['open_to_work', 'Open to work', 'Let recruiters and members know you are looking.'],
              ['open_to_mentor', 'Open to mentor', 'Offer guidance to another member.'],
              ['open_to_collaborate', 'Open to collaborate', 'Looking for project or event collaborators.'],
              ['available_for_speaking', 'Available for speaking', 'Open to talks, panels and workshops.'],
              ['hiring', 'Hiring', 'You are hiring for a role or project.'],
            ] as [$field, $labelText, $hint])
              <label for="{{ $field }}" class="flex items-start gap-3 rounded-lg border border-slate-200 p-3 text-sm text-slate-700">
                <input type="checkbox" name="{{ $field }}" id="{{ $field }}" value="1" @checked(old($field, $profile->{$field})) class="mt-0.5 rounded text-[#FF5C3D] focus:ring-[#FF5C3D]">
                <span><span class="font-medium">{{ $labelText }}</span><span class="block text-xs text-slate-500">{{ $hint }}</span></span>
              </label>
            @endforeach
          </div>
        </section>

        {{-- 10. Email preferences --}}
        <section class="space-y-3" aria-labelledby="sec-email">
          <h2 id="sec-email" class="text-lg font-bold text-[#101E33]">Email preferences</h2>
          <p class="text-sm text-slate-500">Choose which updates you also receive by email. In-app notifications remain enabled.</p>
          <input type="hidden" name="notification_preferences_present" value="1">
          <div class="grid gap-2 sm:grid-cols-2">
            @foreach([
              'events' => 'Events and registrations',
              'opportunities' => 'Opportunities and matches',
              'applications' => 'Application decisions',
              'mentorship' => 'Mentorship and introductions',
              'membership' => 'Membership and account',
              'community' => 'Community activity',
            ] as $category => $categoryLabel)
              <label for="notification-{{ $category }}" class="flex items-start gap-3 rounded-lg border border-slate-200 p-3 text-sm text-slate-700">
                <input type="checkbox" name="notification_preferences[{{ $category }}]" id="notification-{{ $category }}" value="1" @checked(old('notification_preferences.'.$category, $profile->notification_preferences[$category] ?? true)) class="mt-0.5 rounded text-[#FF5C3D] focus:ring-[#FF5C3D]">
                <span>{{ $categoryLabel }}</span>
              </label>
            @endforeach
          </div>
          <label for="weekly_digest" class="flex items-start gap-3 text-sm text-slate-700">
            <input type="checkbox" name="weekly_digest" id="weekly_digest" value="1" @checked(old('weekly_digest', $profile->weekly_digest)) class="mt-0.5 rounded text-[#FF5C3D] focus:ring-[#FF5C3D]">
            <span>Send me the weekly community digest
              <span class="block text-xs text-slate-500">One email each Monday with upcoming events, new articles and opportunities.</span>
            </span>
          </label>
        </section>

        {{-- Sticky action bar --}}
        <div class="sticky bottom-0 -mx-6 sm:-mx-8 -mb-6 sm:-mb-8 px-6 sm:px-8 py-4 bg-white/95 backdrop-blur border-t border-slate-100 rounded-b-2xl flex flex-wrap gap-3">
          <button type="submit" class="px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#101E33] focus-visible:ring-offset-2">Save profile</button>
          @if($profile->username && $profile->exists)
            <a href="{{ $profile->publicUrl() }}" class="px-6 py-3 border border-slate-200 rounded-lg font-medium hover:bg-slate-50 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">View public profile</a>
          @endif
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
    removed: [],
    get available() { return platforms.filter(p => !this.items.find(i => i.key === p.key)); },
    labelOf(key) { return platforms.find(p => p.key === key)?.label || key; },
    add() {
      if (!this.selected) return;
      this.items.push({key: this.selected, value: ''});
      this.removed = this.removed.filter(k => k !== this.selected);
      this.selected = '';
    },
    remove(idx) {
      const [gone] = this.items.splice(idx, 1);
      if (gone && !this.removed.includes(gone.key)) this.removed.push(gone.key);
    }
  };
}
</script>
@endsection
