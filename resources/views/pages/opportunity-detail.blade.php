@extends('layouts.app')
@section('content')
@php $o = $opportunity; @endphp
<section class="bg-[#101E33] text-white py-16 relative overflow-hidden">
  <div class="absolute inset-0 opacity-20">
    <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-[#FF5C3D] blur-3xl"></div>
  </div>
  <div class="max-w-7xl mx-auto px-4 relative">
    <span class="inline-block text-xs px-3 py-1 rounded-full bg-white/10 border border-white/20">{{ $o->category?->name ?? 'Opportunity' }}</span>
    <h1 class="mt-4 text-3xl md:text-4xl font-bold leading-tight">{{ $o->title }}</h1>
    <p class="mt-2 text-slate-300 text-lg">{{ $o->organization }} · {{ $o->location ?? ($o->is_remote ? 'Remote' : '') }}</p>
    <div class="mt-4 flex flex-wrap gap-2">
      @if($o->is_member_only)<span class="text-xs px-3 py-1 rounded-full bg-[#101E33] text-white">Members only</span>@endif
      @if($o->deadline)<span class="text-xs px-3 py-1 rounded-full bg-white/10">Deadline: {{ $o->deadline->format('M j, Y') }}</span>@endif
    </div>
  </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-12 grid md:grid-cols-3 gap-8">
  <div class="md:col-span-2 space-y-6">
    <div class="bg-white rounded-2xl p-8 border border-slate-100 shadow-sm">
      <h2 class="text-2xl font-bold text-[#101E33] mb-3">About this Opportunity</h2>
      <p class="text-slate-600 leading-relaxed text-lg">{{ $o->description ?? 'No description provided.' }}</p>
    </div>

    @if($o->eligibility)
    <div class="bg-white rounded-2xl p-8 border border-slate-100 shadow-sm">
      <h2 class="text-2xl font-bold text-[#101E33] mb-3">Eligibility</h2>
      <p class="text-slate-600 leading-relaxed">{{ $o->eligibility }}</p>
    </div>
    @endif

    @if($o->hub)
    <div class="bg-white rounded-2xl p-8 border border-slate-100 shadow-sm">
      <h2 class="text-2xl font-bold text-[#101E33] mb-3">Posted by</h2>
      <a href="/hubs/{{ $o->hub->slug }}" class="flex items-center gap-4 hover:opacity-80 transition">
        @if($o->hub->logo)
          <img src="{{ $o->hub->logo }}" class="w-14 h-14 rounded-xl object-cover" alt="{{ $o->hub->name }}">
        @else
          <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center font-bold">{{ strtoupper(substr($o->hub->name,0,1)) }}</div>
        @endif
        <div>
          <div class="font-semibold text-[#101E33]">{{ $o->hub->name }}</div>
          <div class="text-sm text-slate-500">{{ $o->hub->city }}{{ $o->hub->state ? ', '.$o->hub->state : '' }}</div>
        </div>
      </a>
    </div>
    @endif
  </div>

  <aside class="space-y-6">
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm sticky top-24">
      <h3 class="text-lg font-bold text-[#101E33] mb-4">Quick Details</h3>
      <dl class="space-y-4 text-sm">
        <div class="flex justify-between gap-3">
          <dt class="text-slate-500">Category</dt>
          <dd class="font-medium text-[#101E33] text-right">{{ $o->category?->name ?? '—' }}</dd>
        </div>
        <div class="flex justify-between gap-3">
          <dt class="text-slate-500">Organization</dt>
          <dd class="font-medium text-[#101E33] text-right">{{ $o->organization ?? '—' }}</dd>
        </div>
        <div class="flex justify-between gap-3">
          <dt class="text-slate-500">Location</dt>
          <dd class="font-medium text-[#101E33] text-right">{{ $o->location ?? ($o->is_remote ? 'Remote' : '—') }}</dd>
        </div>
        <div class="flex justify-between gap-3">
          <dt class="text-slate-500">Work Mode</dt>
          <dd class="font-medium text-[#101E33] text-right">{{ $o->is_remote ? 'Remote' : 'Onsite' }}</dd>
        </div>
        <div class="flex justify-between gap-3">
          <dt class="text-slate-500">Deadline</dt>
          <dd class="font-medium text-[#101E33] text-right">{{ $o->deadline?->format('M j, Y') ?? 'Open' }}</dd>
        </div>
      </dl>
      <div class="mt-6 space-y-2">
        @auth
          @if($hasApplied)
            <div class="block w-full text-center px-6 py-3 bg-green-50 text-green-700 rounded-lg font-semibold">Applied</div>
          @else
            <form method="POST" action="{{ route('opportunities.apply', $o) }}">
              @csrf
              <textarea name="cover_letter" rows="3" placeholder="Optional cover note" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm mb-2"></textarea>
              <button class="block w-full text-center px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Apply on MWTT</button>
            </form>
          @endif
        @else
          <a href="{{ route('login') }}" class="block w-full text-center px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Log in to apply</a>
        @endauth
        @if($o->application_link)
          <a href="{{ $o->application_link }}" target="_blank" class="block w-full text-center px-6 py-3 border border-slate-200 rounded-lg font-medium hover:bg-slate-50 transition">External application link</a>
        @endif
        <a href="/opportunities" class="block w-full text-center px-6 py-3 border border-slate-200 rounded-lg font-medium hover:bg-slate-50 transition">Back to Opportunities</a>
      </div>
    </div>
  </aside>
</div>
@endsection
