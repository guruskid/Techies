@extends('layouts.app')
@section('content')
<section class="bg-[#101E33] text-white py-12">
  <div class="max-w-7xl mx-auto px-4">
    <h1 class="text-3xl font-bold">Members Directory</h1>
    <p class="mt-2 text-slate-300">Find and connect with techies across the network.</p>
  </div>
</section>
<div class="max-w-7xl mx-auto px-4 py-8">
  <form method="GET" class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
    <div class="grid md:grid-cols-12 gap-3 items-end">
      <div class="md:col-span-4">
        <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
        <input name="search" value="{{ $search ?? '' }}" placeholder="Name, title, skill, city..." class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
      </div>
      <div class="md:col-span-3">
        <label class="block text-xs font-medium text-slate-500 mb-1">Technology</label>
        <select name="technology" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          <option value="">All technologies</option>
          @foreach (['Software Development','Frontend Development','Backend Development','Full-Stack Development','Mobile Development','DevOps and Cloud Engineering','Cybersecurity','Data Science','Artificial Intelligence and Machine Learning','UI/UX Design','Product Management'] as $t)
            <option value="{{ $t }}" @selected(($technology ?? '')===$t)>{{ $t }}</option>
          @endforeach
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-medium text-slate-500 mb-1">State</label>
        <select name="state" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          <option value="">All states</option>
          @foreach (['Lagos','Abuja','Rivers','Oyo','Enugu','Edo','Akwa Ibom'] as $st)
            <option value="{{ $st }}" @selected(($state ?? '')===$st)>{{ $st }}</option>
          @endforeach
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-medium text-slate-500 mb-1">Experience</label>
        <select name="experience" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          <option value="">All levels</option>
          @foreach (['Student','Beginner','Intermediate','Experienced','Expert'] as $e)
            <option value="{{ $e }}" @selected(($experience ?? '')===$e)>{{ $e }}</option>
          @endforeach
        </select>
      </div>
      <div class="md:col-span-1 flex gap-2">
        <button class="flex-1 px-4 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Search</button>
      </div>
    </div>
    @if(($search ?? '') || ($technology ?? '') || ($state ?? '') || ($experience ?? ''))
      <div class="mt-3 text-sm text-slate-500">Showing results. <a href="/members" class="text-[#FF5C3D] hover:underline">Clear filters</a></div>
    @endif
  </form>

  <div class="grid md:grid-cols-3 gap-4 mt-8">
    @forelse($members as $m)
      <a href="{{ route('members.show', $m->profile?->username ?? $m->id) }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition flex gap-4 items-center">
        @if($m->profile?->avatar)
          <img src="{{ $m->profile->avatar }}" class="w-12 h-12 rounded-full object-cover" alt="{{ $m->name }}">
        @else
          <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center font-bold">{{ strtoupper(substr($m->name,0,1)) }}</div>
        @endif
        <div class="min-w-0">
          <div class="font-semibold text-[#101E33] truncate">{{ $m->name }}</div>
          <div class="text-sm text-slate-500 truncate">{{ $m->profile?->professional_title ?? 'Member' }}</div>
          <div class="text-xs text-slate-400 truncate">{{ $m->profile?->technology_category }}</div>
        </div>
      </a>
    @empty
      <p class="text-slate-500 col-span-3">No members found.</p>
    @endforelse
  </div>
  <div class="mt-8">{{ $members->links() }}</div>
</div>
@endsection
