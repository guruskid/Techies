@extends('layouts.app')
@section('content')
@php $p = $profile; @endphp
<section class="bg-[#101E33] text-white py-16">
  <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center gap-6">
    <div class="w-28 h-28 rounded-full bg-white/10 border-4 border-white/20 flex items-center justify-center text-4xl font-bold">
      {{ strtoupper(substr($user->name,0,1)) }}
    </div>
    <div>
      <h1 class="text-3xl font-bold">{{ $user->name }}</h1>
      <p class="text-slate-300 mt-1">{{ $p->professional_title ?? 'Member' }}</p>
      @if($p->headline)<p class="text-slate-400 text-sm mt-1">{{ $p->headline }}</p>@endif
      <p class="text-sm text-slate-400 mt-1">{{ $p->city }}{{ $p->state ? ', '.$p->state : '' }}{{ $p->country ? ', '.$p->country : '' }}</p>
      @if(!$p->is_public)<span class="inline-block mt-2 text-xs px-2 py-1 rounded-full bg-yellow-500/20 text-yellow-300">Private profile</span>@endif
    </div>
  </div>
</section>
<div class="max-w-7xl mx-auto px-4 py-12 grid md:grid-cols-3 gap-8">
  <div class="md:col-span-2 space-y-6">
    @if($p->bio)
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33] mb-2">About</h2>
      <p class="text-slate-600 leading-relaxed">{{ $p->bio }}</p>
    </div>
    @endif
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33] mb-3">Professional Details</h2>
      <dl class="grid sm:grid-cols-2 gap-4 text-sm">
        <div><dt class="text-slate-500">Experience Level</dt><dd class="font-medium text-[#101E33]">{{ $p->experience_level ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">Technology Category</dt><dd class="font-medium text-[#101E33]">{{ $p->technology_category ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">Current Organization</dt><dd class="font-medium text-[#101E33]">{{ $p->current_organization ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">Education</dt><dd class="font-medium text-[#101E33]">{{ $p->education ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">Certifications</dt><dd class="font-medium text-[#101E33]">{{ $p->certifications ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">Languages</dt><dd class="font-medium text-[#101E33]">{{ $p->languages ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">Availability</dt><dd class="font-medium text-[#101E33]">{{ $p->availability ?? '—' }}</dd></div>
      </dl>
    </div>
  </div>
  <aside class="space-y-6">
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33] mb-3">Links</h2>
      <ul class="space-y-2 text-sm">
        @if($p->linkedin)<li><a href="{{ $p->linkedin }}" class="text-[#FF5C3D] hover:underline" target="_blank">LinkedIn</a></li>@endif
        @if($p->github)<li><a href="{{ $p->github }}" class="text-[#FF5C3D] hover:underline" target="_blank">GitHub</a></li>@endif
        @if(!$p->linkedin && !$p->github && !$p->portfolio && !$p->website)<li class="text-slate-400">No links shared.</li>@endif
      </ul>
    </div>
    @if($user->chapters->count())
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33] mb-3">Chapters</h2>
      <ul class="space-y-1 text-sm">
        @foreach($user->chapters as $ch)
          <li><a href="/chapters/{{ $ch->slug }}" class="text-[#FF5C3D] hover:underline">{{ $ch->name }}</a></li>
        @endforeach
      </ul>
    </div>
    @endif
    @if($user->skills->count())
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33] mb-3">Skills</h2>
      <div class="flex flex-wrap gap-2">
        @foreach($user->skills as $sk)
          <span class="px-3 py-1 rounded-full bg-[#FF5C3D]/10 text-[#FF5C3D] text-xs font-medium">{{ $sk->name }}</span>
        @endforeach
      </div>
    </div>
    @endif

    @if($user->workExperiences->count())
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33] mb-3">Work Experience</h2>
      <div class="space-y-4">
        @foreach($user->workExperiences as $we)
          <div>
            <div class="font-semibold text-[#101E33]">{{ $we->title }}</div>
            <div class="text-sm text-slate-500">{{ $we->company }} · {{ $we->location }}</div>
            <div class="text-xs text-slate-400">{{ $we->start_date?->format('M Y') }} – {{ $we->is_current ? 'Present' : $we->end_date?->format('M Y') }}</div>
            @if($we->description)<p class="text-sm text-slate-600 mt-1">{{ $we->description }}</p>@endif
          </div>
        @endforeach
      </div>
    </div>
    @endif

    @if($user->projects->count())
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33] mb-3">Projects</h2>
      <div class="space-y-4">
        @foreach($user->projects as $pr)
          <div>
            <div class="font-semibold text-[#101E33]">{{ $pr->title }}</div>
            @if($pr->technologies)<div class="text-xs text-[#FF5C3D] mt-1">{{ $pr->technologies }}</div>@endif
            @if($pr->description)<p class="text-sm text-slate-600 mt-1">{{ $pr->description }}</p>@endif
            @if($pr->url)<a href="{{ $pr->url }}" target="_blank" class="text-sm text-[#FF5C3D] hover:underline mt-1 inline-block">View project →</a>@endif
          </div>
        @endforeach
      </div>
    </div>
    @endif

    @if($user->interests->count())
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33] mb-3">Interests</h2>
      <div class="flex flex-wrap gap-2">
        @foreach($user->interests as $it)
          <span class="px-3 py-1 rounded-full bg-[#101E33]/10 text-[#101E33] text-xs font-medium">{{ $it->name }}</span>
        @endforeach
      </div>
    </div>
    @endif

    @if($p->likes)
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33] mb-2">Likes & Hobbies</h2>
      <p class="text-slate-600 text-sm">{{ $p->likes }}</p>
    </div>
    @endif

    @if($p->areas_of_interest)
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33] mb-3">Interests</h2>
      <div class="flex flex-wrap gap-2">
        @foreach((array)$p->areas_of_interest as $i)
          <span class="px-3 py-1 rounded-full bg-[#FF5C3D]/10 text-[#FF5C3D] text-xs font-medium">{{ $i }}</span>
        @endforeach
      </div>
    </div>
    @endif
  </aside>
</div>
@endsection
