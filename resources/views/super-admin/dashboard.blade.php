@extends('layouts.admin')
@section('content')
@php
  $me = auth()->user();
  $card = 'bg-white rounded-2xl border border-slate-100 shadow-sm';
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]';
  $row = 'hover:bg-slate-50 transition focus:outline-none focus-visible:bg-slate-50 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#FF5C3D]';
  $ghostBtn = 'px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-sm hover:bg-white/15 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white';

  $queueUrl = route('admin.content.index', ['approval' => 'super_admin', 'status' => 'pending']);

  // Stat groups. Cards with a URL are clickable.
  $statGroups = [
    'People' => [
      ['Total users',     $stats['total_users'],     'text-[#101E33]', route('super-admin.users.index')],
      ['Active members',  $stats['active_members'],  'text-green-700', route('admin.members.index')],
      ['Staff accounts',  $stats['staff'],           'text-[#FF5C3D]', route('super-admin.users.index')],
      ['Suspended',       $stats['suspended'],       'text-red-700',   null],
    ],
    'Content and community' => [
      ['Pending content',     $stats['pending_content'],       'text-amber-700', route('admin.content.index', ['status' => 'pending'])],
      ['Super admin topics',  $stats['super_admin_content'],   'text-[#101E33]', $queueUrl],
      ['Published articles',  $stats['published_content'],     'text-green-700', null],
      ['Pending hubs',        $stats['pending_hubs'],          'text-amber-700', route('admin.hubs.index', ['status' => 'pending'])],
      ['Events',              $stats['events'],                'text-slate-800', route('admin.events.index')],
      ['Opportunities',       $stats['opportunities'],         'text-slate-800', route('admin.opportunities.index')],
    ],
  ];

  $maxRole = max(1, (int) $roles->max('users_count'));
  $roleName = fn ($name) => ucfirst(str_replace('_', ' ', $name));
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 pb-12">

  {{-- Header --}}
  <div class="bg-[#101E33] text-white rounded-3xl p-6 sm:p-8 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold">Super admin console</h1>
        <p class="mt-2 text-slate-300 max-w-2xl">Welcome, {{ $me->name }}. Manage staff roles, content approvals, settings and the audit trail.</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.dashboard') }}" class="{{ $ghostBtn }}">Operations dashboard</a>
        <a href="{{ route('profile.edit') }}" class="{{ $ghostBtn }}">Edit my profile</a>
        @if($me->profile?->username)
          <a href="{{ $me->profile->publicUrl() }}" class="{{ $ghostBtn }}">View public profile</a>
        @endif
      </div>
    </div>
  </div>

  {{-- Needs attention (only when something is waiting) --}}
  @php
    $attention = array_filter([
      ['n' => $stats['super_admin_content'], 'one' => 'topic', 'text' => 'waiting for your approval', 'url' => $queueUrl],
      ['n' => $stats['pending_content'],     'one' => 'article', 'text' => 'in the review queue',    'url' => route('admin.content.index', ['status' => 'pending'])],
      ['n' => $stats['pending_hubs'],        'one' => 'hub',     'text' => 'waiting for approval',   'url' => route('admin.hubs.index', ['status' => 'pending'])],
    ], fn ($a) => (int) $a['n'] > 0);
  @endphp
  @if($attention)
    <div class="mb-8 rounded-xl border border-[#FF5C3D]/30 bg-[#FF5C3D]/5 p-4 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-6" role="status">
      <p class="text-sm font-semibold text-[#101E33]">Waiting on you</p>
      <div class="flex flex-wrap gap-2">
        @foreach($attention as $a)
          <a href="{{ $a['url'] }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-[#FF5C3D]/40 text-sm text-[#101E33] hover:border-[#FF5C3D] {{ $focus }}">
            <span class="font-bold text-[#FF5C3D]">{{ $a['n'] }}</span>
            {{ \Illuminate\Support\Str::plural($a['one'], $a['n']) }} {{ $a['text'] }}
          </a>
        @endforeach
      </div>
    </div>
  @endif

  {{-- Stats, grouped --}}
  <div class="space-y-6 mb-8">
    @foreach($statGroups as $group => $items)
      <section aria-label="{{ $group }}">
        <h2 class="text-sm font-bold text-[#101E33] mb-2">{{ $group }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 {{ count($items) > 4 ? 'lg:grid-cols-6' : 'lg:grid-cols-4' }} gap-3">
          @foreach($items as [$label, $value, $colour, $url])
            @php $tag = $url ? 'a' : 'div'; @endphp
            <{{ $tag }} @if($url) href="{{ $url }}" @endif
              class="block {{ $card }} p-4 {{ $url ? 'hover:border-slate-300 '.$focus : '' }}">
              <div class="text-xs text-slate-600">{{ $label }}</div>
              <div class="mt-1 text-2xl font-bold tabular-nums {{ $colour }}">{{ number_format((int) $value) }}</div>
            </{{ $tag }}>
          @endforeach
        </div>
      </section>
    @endforeach
  </div>

  <div class="grid lg:grid-cols-3 gap-6 items-start">

    {{-- Main column --}}
    <div class="lg:col-span-2 space-y-6">

      <section class="{{ $card }}" aria-labelledby="queue-h">
        <div class="flex items-start justify-between gap-4 p-5 sm:p-6 border-b border-slate-100">
          <div>
            <h2 id="queue-h" class="text-lg font-bold text-[#101E33]">Super admin approval queue</h2>
            <p class="text-sm text-slate-500">Topics reserved for super administrator review.</p>
          </div>
          <a href="{{ $queueUrl }}" class="shrink-0 text-sm font-medium text-[#FF5C3D] hover:underline {{ $focus }} rounded">View all</a>
        </div>
        <ul class="divide-y divide-slate-100">
          @forelse($approvalQueue as $article)
            <li>
              <a href="{{ route('admin.content.show', $article) }}" class="flex items-center justify-between gap-3 p-4 sm:px-6 {{ $row }}">
                <div class="min-w-0">
                  <p class="font-medium text-[#101E33] truncate">{{ $article->title }}</p>
                  <p class="text-xs text-slate-500 mt-1">
                    By {{ $article->author?->name ?? 'Unknown author' }}, {{ $article->topicLabel() }}, submitted {{ ($article->submitted_at ?? $article->updated_at)->diffForHumans() }}
                  </p>
                </div>
                <span class="shrink-0 text-sm font-medium text-[#FF5C3D]">Review</span>
              </a>
            </li>
          @empty
            <li class="p-8 text-center">
              <p class="text-sm font-medium text-[#101E33]">Nothing needs your approval</p>
              <p class="text-sm text-slate-500 mt-1">Reserved topics will appear here when members submit them.</p>
            </li>
          @endforelse
        </ul>
      </section>

      <section class="{{ $card }}" aria-labelledby="audit-h">
        <div class="flex items-center justify-between gap-4 p-5 sm:p-6 border-b border-slate-100">
          <h2 id="audit-h" class="text-lg font-bold text-[#101E33]">Recent audit activity</h2>
          <a href="{{ route('super-admin.audit-logs.index') }}" class="text-sm font-medium text-[#FF5C3D] hover:underline {{ $focus }} rounded">Open full log</a>
        </div>
        <ul class="divide-y divide-slate-100">
          @forelse($recentLogs as $log)
            <li class="p-4 sm:px-6 flex items-start sm:items-center justify-between gap-3 text-sm">
              <p class="min-w-0">
                <span class="font-medium text-[#101E33]">{{ $log->action }}</span>
                <span class="text-slate-600">by {{ $log->user?->name ?? 'System' }}</span>
                @if($log->model_type)
                  <span class="text-slate-500">on {{ class_basename($log->model_type) }} #{{ $log->model_id }}</span>
                @endif
              </p>
              <time class="shrink-0 text-xs text-slate-500" datetime="{{ $log->created_at->toIso8601String() }}" title="{{ $log->created_at->format('M j, Y g:i A') }}">{{ $log->created_at->diffForHumans() }}</time>
            </li>
          @empty
            <li class="p-8 text-center text-sm text-slate-500">No audit activity yet. Admin actions will be logged here.</li>
          @endforelse
        </ul>
      </section>
    </div>

    {{-- Sidebar --}}
    <aside class="space-y-6" aria-label="Console tools">

      <section class="{{ $card }} p-5">
        <h2 class="font-bold text-[#101E33] mb-3">Management</h2>
        <ul class="space-y-2">
          @foreach([
            ['Users and roles',         route('super-admin.users.index')],
            ['Roles and permissions',   route('super-admin.roles.index')],
            ['Organisation settings',   route('super-admin.settings.edit')],
            ['Audit logs',              route('super-admin.audit-logs.index')],
            ['Management center',       route('admin.manage')],
          ] as [$name, $url])
            <li>
              <a href="{{ $url }}" class="block px-4 py-2.5 rounded-lg bg-slate-50 hover:bg-slate-100 text-sm font-medium text-slate-800 {{ $focus }}">{{ $name }}</a>
            </li>
          @endforeach
        </ul>
      </section>

      <section class="{{ $card }} p-5">
        <h2 class="font-bold text-[#101E33] mb-3">Role distribution</h2>
        <ul class="space-y-3">
          @forelse($roles as $role)
            <li>
              <div class="flex items-center justify-between text-sm mb-1">
                <span class="text-slate-700">{{ $roleName($role->name) }}</span>
                <span class="text-xs font-medium text-slate-600 tabular-nums">{{ $role->users_count }}</span>
              </div>
              <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden" aria-hidden="true">
                <div class="h-full bg-[#101E33]" style="width: {{ round($role->users_count / $maxRole * 100) }}%"></div>
              </div>
            </li>
          @empty
            <li class="text-sm text-slate-500">No roles defined yet.</li>
          @endforelse
        </ul>
      </section>

      <section class="{{ $card }} p-5">
        <h2 class="font-bold text-[#101E33] mb-3">Newest accounts</h2>
        <ul class="space-y-1">
          @forelse($recentUsers as $account)
            <li>
              <a href="{{ route('super-admin.users.index', ['search' => $account->email]) }}" class="flex items-center gap-3 p-2 rounded-lg {{ $row }}">
                <span class="w-8 h-8 shrink-0 rounded-full bg-[#101E33] text-white flex items-center justify-center text-xs font-bold" aria-hidden="true">{{ strtoupper(substr($account->name, 0, 1)) }}</span>
                <span class="min-w-0 text-sm">
                  <span class="block truncate font-medium text-slate-800">{{ $account->name }}</span>
                  <span class="block text-xs text-slate-500">
                    {{ ($r = $account->roles->first()?->name) ? $roleName($r) : 'No role' }}, joined {{ $account->created_at->diffForHumans() }}
                  </span>
                </span>
              </a>
            </li>
          @empty
            <li class="text-sm text-slate-500">No accounts yet.</li>
          @endforelse
        </ul>
      </section>
    </aside>
  </div>
</div>
@endsection