@extends('layouts.admin')
@section('content')
@php
  $user = auth()->user();
  $pendingContent = $stats['pending_content'] ?? 0;
  $pendingHubs = $stats['pending_hubs'] ?? null;

  // Optional: map a stat key to the page that lists it. Unmapped stats render as plain cards.
  $statLinks = [
    'members'       => route('admin.members.index'),
    'hubs'          => route('admin.hubs.index'),
    'events'        => route('admin.events.index'),
    'chapters'      => route('admin.chapters.index'),
    'opportunities' => route('admin.opportunities.index'),
    'pending_content' => route('admin.content.index'),
    'pending_hubs'  => route('admin.hubs.index', ['status' => 'pending']),
  ];

  $manage = [
    'People and places' => [
      ['Members',  route('admin.members.index')],
      ['Hubs',     route('admin.hubs.index')],
      ['Chapters', route('admin.chapters.index')],
    ],
    'Community content' => [
      ['Events',        route('admin.events.index')],
      ['Opportunities', route('admin.opportunities.index')],
      ['Categories',    route('admin.categories.index')],
      ['Review queue',  route('admin.content.index')],
    ],
  ];
@endphp

<div class="max-w-7xl mx-auto mt-8 px-4 pb-12">

  {{-- Header --}}
  <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-6">
    <div>
      <h1 class="text-2xl font-bold text-[#101E33]">Admin dashboard</h1>
      <p class="text-sm text-slate-500 mt-1">Members, hubs, chapters and community content in one place.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('admin.manage') }}"
         class="px-4 py-2 bg-[#101E33] text-white rounded-lg text-sm font-medium hover:bg-[#1a2f50] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2">
        Management center
      </a>
      <a href="{{ route('profile.edit') }}"
         class="px-4 py-2 border border-slate-200 bg-white rounded-lg text-sm hover:border-slate-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
        Edit my profile
      </a>
      @if($user->profile?->username)
        <a href="{{ $user->profile->publicUrl() }}"
           class="px-4 py-2 border border-slate-200 bg-white rounded-lg text-sm hover:border-slate-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
          View public profile
        </a>
      @endif
      @if($user->isSuperAdmin())
        <a href="{{ route('super-admin.dashboard') }}"
           class="px-4 py-2 border border-[#101E33] text-[#101E33] rounded-lg text-sm font-medium hover:bg-[#101E33] hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
          Super admin console
        </a>
      @endif
    </div>
  </div>

  {{-- Needs attention: only shown when there is work waiting --}}
  @if($pendingContent > 0 || ($pendingHubs ?? 0) > 0)
    <div class="mb-6 rounded-xl border border-[#FF5C3D]/30 bg-[#FF5C3D]/5 p-4 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-6" role="status">
      <p class="text-sm font-semibold text-[#101E33]">Waiting on you</p>
      <div class="flex flex-wrap gap-2">
        @if($pendingContent > 0)
          <a href="{{ route('admin.content.index') }}"
             class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-[#FF5C3D]/40 text-sm text-[#101E33] hover:border-[#FF5C3D] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
            <span class="font-bold text-[#FF5C3D]">{{ $pendingContent }}</span>
            {{ \Illuminate\Support\Str::plural('article', $pendingContent) }} to review
          </a>
        @endif
        @if(($pendingHubs ?? 0) > 0)
          <a href="{{ route('admin.hubs.index', ['status' => 'pending']) }}"
             class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-[#FF5C3D]/40 text-sm text-[#101E33] hover:border-[#FF5C3D] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
            <span class="font-bold text-[#FF5C3D]">{{ $pendingHubs }}</span>
            {{ \Illuminate\Support\Str::plural('hub', $pendingHubs) }} to approve
          </a>
        @else
          <a href="{{ route('admin.hubs.index', ['status' => 'pending']) }}"
             class="inline-flex items-center px-3 py-1.5 rounded-full bg-white border border-[#FF5C3D]/40 text-sm text-[#101E33] hover:border-[#FF5C3D] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
            Review pending hubs
          </a>
        @endif
      </div>
    </div>
  @endif

  {{-- Stats --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    @foreach($stats as $key => $value)
      @php
        $label = ucfirst(str_replace('_', ' ', $key));
        $href  = $statLinks[$key] ?? null;
        $isPending = str_starts_with($key, 'pending') && (int) $value > 0;
        $tag = $href ? 'a' : 'div';
      @endphp
      <{{ $tag }} @if($href) href="{{ $href }}" @endif
        class="block bg-white rounded-xl p-5 border {{ $isPending ? 'border-[#FF5C3D]/40' : 'border-slate-100' }} shadow-sm {{ $href ? 'hover:border-slate-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]' : '' }}">
        <div class="text-3xl font-bold tabular-nums {{ $isPending ? 'text-[#FF5C3D]' : 'text-[#101E33]' }}">
          {{ number_format((int) $value) }}
        </div>
        <div class="text-sm text-slate-500 mt-1">{{ $label }}</div>
      </{{ $tag }}>
    @endforeach
  </div>

  {{-- Main area --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

    {{-- Review queue (primary) --}}
    <section class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm" aria-labelledby="queue-heading">
      <div class="flex items-start justify-between gap-4 p-5 sm:p-6 border-b border-slate-100">
        <div>
          <h2 id="queue-heading" class="text-lg font-bold text-[#101E33]">Articles awaiting review</h2>
          <p class="text-sm text-slate-500 mt-0.5">Approve, request changes or unpublish member submissions.</p>
        </div>
        <a href="{{ route('admin.content.index') }}"
           class="shrink-0 text-sm font-medium text-[#FF5C3D] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded">
          Open full queue
        </a>
      </div>

      <ul class="divide-y divide-slate-100">
        @forelse($pendingArticles as $article)
          <li>
            <a href="{{ route('admin.content.show', $article) }}"
               class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 sm:px-6 hover:bg-slate-50 focus:outline-none focus-visible:bg-slate-50 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#FF5C3D]">
              <div class="min-w-0">
                <p class="font-medium text-[#101E33] truncate">{{ $article->title }}</p>
                <p class="mt-1 text-xs text-slate-500">
                  By {{ $article->author?->name ?? 'Unknown author' }},
                  {{ $article->topicLabel() }},
                  submitted {{ ($article->submitted_at ?? $article->updated_at)->diffForHumans() }}
                </p>
              </div>
              <span class="shrink-0 self-start sm:self-center px-2.5 py-1 rounded-full text-xs font-medium {{ $article->requiresSuperAdminApproval() ? 'bg-[#101E33] text-white' : 'bg-[#FF5C3D]/10 text-[#FF5C3D]' }}">
                {{ $article->approvalLabel() }}
              </span>
            </a>
          </li>
        @empty
          <li class="p-8 text-center">
            <p class="text-sm font-medium text-[#101E33]">The queue is clear</p>
            <p class="text-sm text-slate-500 mt-1">New member submissions will appear here for review.</p>
          </li>
        @endforelse
      </ul>
    </section>

    {{-- Manage (secondary) --}}
    <aside class="space-y-6" aria-label="Manage">
      @foreach($manage as $group => $links)
        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
          <h2 class="text-sm font-bold text-[#101E33] mb-3">{{ $group }}</h2>
          <ul class="grid grid-cols-2 gap-2">
            @foreach($links as [$name, $url])
              <li>
                <a href="{{ $url }}"
                   class="block px-3 py-2 rounded-lg border border-slate-200 text-sm text-slate-700 hover:border-[#101E33] hover:text-[#101E33] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
                  {{ $name }}
                </a>
              </li>
            @endforeach
          </ul>
        </section>
      @endforeach
    </aside>

  </div>
</div>
@endsection