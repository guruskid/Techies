@extends('layouts.app')
@section('content')
@php
  $user = auth()->user();
  $firstName = \Illuminate\Support\Str::of($user->name)->before(' ');
  $card = 'bg-white rounded-2xl border border-slate-100 shadow-sm';
  $row = 'hover:bg-slate-50 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#FF5C3D]';
  $viewAll = 'text-sm font-medium text-[#FF5C3D] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
@endphp

<div class="max-w-7xl mx-auto px-4 py-8">

  {{-- Welcome --}}
  <div class="bg-[#101E33] text-white rounded-3xl p-6 sm:p-8 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl md:text-3xl font-bold">Welcome back, {{ $firstName }}</h1>
      <p class="text-slate-300 mt-1">Here is what is happening in your community.</p>
    </div>
    @if($user->profile?->username)
      <a href="{{ $user->profile->publicUrl() }}" class="px-5 py-2.5 bg-[#FF5C3D] rounded-lg font-semibold hover:opacity-90 transition text-center focus:outline-none focus-visible:ring-2 focus-visible:ring-white">View my profile</a>
    @else
      <a href="{{ route('profile.edit') }}" class="px-5 py-2.5 bg-[#FF5C3D] rounded-lg font-semibold hover:opacity-90 transition text-center focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Complete my profile</a>
    @endif
  </div>

  {{-- Stats --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <a href="{{ route('profile.edit') }}" class="block {{ $card }} p-5 hover:shadow-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
      <div class="text-sm text-slate-600">Profile completion</div>
      <div class="mt-2 text-2xl font-bold text-[#FF5C3D]">{{ $completion }}%</div>
      <div class="mt-2 h-2 bg-slate-100 rounded-full overflow-hidden" role="progressbar" aria-valuenow="{{ $completion }}" aria-valuemin="0" aria-valuemax="100" aria-label="Profile completion">
        <div class="h-full bg-[#FF5C3D]" style="width: {{ $completion }}%"></div>
      </div>
      <div class="mt-2 text-xs font-medium text-[#FF5C3D]">{{ $completion >= 100 ? 'Edit profile' : 'Finish your profile' }}</div>
    </a>

    <div class="{{ $card }} p-5">
      <div class="text-sm text-slate-600">Membership</div>
      <div class="mt-2 text-2xl font-bold text-[#101E33]">{{ ucfirst($user->status) }}</div>
      @if($user->status === 'active')
        <div class="mt-2 text-xs text-slate-500">Verified member</div>
      @endif
    </div>

    <a href="/events" class="block {{ $card }} p-5 hover:shadow-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
      <div class="text-sm text-slate-600">Upcoming events</div>
      <div class="mt-2 text-2xl font-bold text-[#101E33]">{{ $upcomingEvents->count() }}</div>
      <div class="mt-2 text-xs text-slate-500">Browse events</div>
    </a>

    <a href="/events" class="block {{ $card }} p-5 hover:shadow-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
      <div class="text-sm text-slate-600">Events you are attending</div>
      <div class="mt-2 text-2xl font-bold text-[#101E33]">{{ $registeredEvents }}</div>
      <div class="mt-2 text-xs text-slate-500">Registered</div>
    </a>
  </div>

  {{-- Events + Hubs --}}
  <div class="grid md:grid-cols-3 gap-6">
    <section class="md:col-span-2 {{ $card }} p-6" aria-labelledby="events-h">
      <div class="flex items-center justify-between mb-4">
        <h2 id="events-h" class="text-lg font-bold text-[#101E33]">Upcoming events</h2>
        <a href="/events" class="{{ $viewAll }}">View all</a>
      </div>
      <ul class="space-y-1">
        @forelse($upcomingEvents as $e)
          @php $date = \Illuminate\Support\Carbon::parse($e->start_date); @endphp
          <li>
            <a href="/events/{{ $e->slug }}" class="flex items-center gap-4 p-3 rounded-xl {{ $row }}">
              <div class="w-12 h-12 shrink-0 rounded-xl bg-[#FF5C3D]/10 text-[#FF5C3D] flex flex-col items-center justify-center leading-none">
                <span class="text-[10px] font-semibold">{{ $date->format('M') }}</span>
                <span class="text-lg font-bold">{{ $date->format('j') }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="font-medium text-[#101E33] truncate">{{ $e->title }}</div>
                <div class="text-xs text-slate-500">
                  {{ $date->format('l, M j, Y') }}{{ $e->is_online ? ', Online' : ($e->city ? ', '.$e->city : '') }}
                </div>
              </div>
              <span class="hidden sm:inline text-xs font-medium text-[#FF5C3D]">View details</span>
            </a>
          </li>
        @empty
          <li class="text-sm text-slate-500">No upcoming events right now. <a href="/events" class="text-[#FF5C3D] hover:underline">Browse past events</a>.</li>
        @endforelse
      </ul>
    </section>

    <section class="{{ $card }} p-6" aria-labelledby="hubs-h">
      <div class="flex items-center justify-between mb-4">
        <h2 id="hubs-h" class="text-lg font-bold text-[#101E33]">Recommended hubs</h2>
        <a href="/hubs" class="{{ $viewAll }}">View all</a>
      </div>
      <ul class="space-y-1">
        @forelse($recommendedHubs as $h)
          <li>
            <a href="/hubs/{{ $h->slug }}" class="flex items-center gap-3 p-2 rounded-xl {{ $row }}">
              @if($h->logo)
                <img src="{{ $h->logo }}" class="w-10 h-10 rounded-lg object-cover" alt="">
              @else
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center font-bold" aria-hidden="true">{{ strtoupper(substr($h->name,0,1)) }}</div>
              @endif
              <div class="flex-1 min-w-0">
                <div class="font-medium text-[#101E33] text-sm truncate">{{ $h->name }}</div>
                @if($h->city)<div class="text-xs text-slate-500 truncate">{{ $h->city }}</div>@endif
              </div>
            </a>
          </li>
        @empty
          <li class="text-sm text-slate-500">No hubs to recommend yet.</li>
        @endforelse
      </ul>
    </section>
  </div>

  {{-- Opportunities + Announcements --}}
  <div class="grid md:grid-cols-3 gap-6 mt-6">
    <section class="md:col-span-2 {{ $card }} p-6" aria-labelledby="opps-h">
      <div class="flex items-start justify-between gap-3 mb-4">
        <div>
          <h2 id="opps-h" class="text-lg font-bold text-[#101E33]">Recommended opportunities</h2>
          <p class="text-sm text-slate-500">
            @if($applicationCount > 0)
              You have submitted {{ $applicationCount }} {{ \Illuminate\Support\Str::plural('application', $applicationCount) }}.
            @else
              Jobs and programmes picked for you.
            @endif
          </p>
        </div>
        <a href="/opportunities" class="shrink-0 {{ $viewAll }}">View all</a>
      </div>
      <ul class="space-y-3">
        @forelse($opportunities as $o)
          @php $meta = array_filter([$o->organization, $o->location ?: ($o->is_remote ? 'Remote' : null)]); @endphp
          <li>
            <a href="/opportunities/{{ $o->slug }}" class="block p-3 rounded-xl border border-slate-100 {{ $row }}">
              <div class="font-medium text-[#101E33]">{{ $o->title }}</div>
              @if($meta)<div class="text-xs text-slate-500 mt-1">{{ implode(', ', $meta) }}</div>@endif
            </a>
          </li>
        @empty
          <li class="text-sm text-slate-500">No opportunities yet. Check back soon.</li>
        @endforelse
      </ul>
    </section>

    <section class="{{ $card }} p-6" aria-labelledby="ann-h">
      <h2 id="ann-h" class="text-lg font-bold text-[#101E33] mb-4">Announcements</h2>
      <ul class="space-y-3">
        @forelse($announcements as $a)
          <li class="p-3 rounded-xl bg-slate-50">
            <div class="font-medium text-[#101E33] text-sm">{{ $a->title }}</div>
            <p class="text-xs text-slate-600 mt-1">{{ \Illuminate\Support\Str::limit($a->body, 80) }}</p>
            @if($a->created_at)<p class="text-[11px] text-slate-500 mt-1">{{ $a->created_at->diffForHumans() }}</p>@endif
          </li>
        @empty
          <li class="text-sm text-slate-500">No announcements yet.</li>
        @endforelse
      </ul>
    </section>
  </div>

  {{-- My content --}}
  <section class="{{ $card }} p-6 mt-6" aria-labelledby="content-h">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
      <div>
        <h2 id="content-h" class="text-lg font-bold text-[#101E33]">My articles</h2>
        <p class="text-sm text-slate-500">Write articles and submit them for review.</p>
      </div>
      <div class="flex gap-2">
        <a href="{{ route('member.content.index') }}" class="px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">Manage articles</a>
        <a href="{{ route('member.content.create') }}" class="px-4 py-2 text-sm bg-[#FF5C3D] text-white rounded-lg hover:opacity-90 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#101E33] focus-visible:ring-offset-2">Write an article</a>
      </div>
    </div>

    <dl class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
      @foreach([
        ['Drafts', $contentStats['drafts'], 'text-slate-700'],
        ['In review', $contentStats['pending'], 'text-amber-700'],
        ['Published', $contentStats['approved'], 'text-green-700'],
        ['Changes requested', $contentStats['rejected'], 'text-red-700'],
      ] as [$label, $value, $colour])
        <div class="rounded-xl bg-slate-50 p-4">
          <dt class="text-xs text-slate-600">{{ $label }}</dt>
          <dd class="mt-1 text-xl font-bold {{ $colour }}">{{ $value }}</dd>
        </div>
      @endforeach
    </dl>

    <ul class="space-y-1">
      @forelse($latestArticles as $article)
        <li>
          <a href="{{ $article->isPublished() ? route('articles.show', $article) : route('member.content.edit', $article) }}"
             class="flex items-center justify-between gap-3 p-3 rounded-xl {{ $row }}">
            <div class="min-w-0">
              <div class="text-sm font-medium text-[#101E33] truncate">{{ $article->title }}</div>
              <div class="text-xs text-slate-500">{{ $article->topicLabel() }}, updated {{ $article->updated_at->diffForHumans() }}</div>
            </div>
            <span class="shrink-0 px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-700">{{ $article->statusLabel() }}</span>
          </a>
        </li>
      @empty
        <li class="text-sm text-slate-500">You have no articles yet. <a href="{{ route('member.content.create') }}" class="text-[#FF5C3D] hover:underline">Write your first article</a>.</li>
      @endforelse
    </ul>
  </section>

  {{-- Recently joined --}}
  <section class="{{ $card }} p-6 mt-6" aria-labelledby="members-h">
    <div class="flex items-center justify-between mb-4">
      <h2 id="members-h" class="text-lg font-bold text-[#101E33]">Recently joined techies</h2>
      <a href="/members" class="{{ $viewAll }}">View all</a>
    </div>
    <ul class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
      @forelse($recentMembers as $m)
        <li>
          @if($m->profile?->username)
            <a href="{{ $m->profile->publicUrl() }}" class="flex items-center gap-2 p-2 rounded-xl {{ $row }}">
          @else
            <div class="flex items-center gap-2 p-2">
          @endif
              <div class="w-9 h-9 shrink-0 rounded-full bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center text-sm font-bold" aria-hidden="true">{{ strtoupper(substr($m->name,0,1)) }}</div>
              <div class="text-sm font-medium text-[#101E33] truncate">{{ $m->name }}</div>
          @if($m->profile?->username)
            </a>
          @else
            </div>
          @endif
        </li>
      @empty
        <li class="text-sm text-slate-500 col-span-full">No members yet.</li>
      @endforelse
    </ul>
  </section>

</div>
@endsection