@extends('layouts.app')
@section('content')
@php
  $current = request('status');
  $statuses = ['active' => 'Active', 'suspended' => 'Suspended'];
  $badge = [
    'active'    => 'bg-green-50 text-green-700',
    'suspended' => 'bg-red-50 text-red-700',
  ];
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-1';
  $field = 'border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $btn = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold border transition '.$focus;
  $btnView    = $btn.' border-slate-200 text-slate-700 bg-white hover:bg-slate-50';
  $btnRestore = $btn.' border-green-600 bg-green-600 text-white hover:bg-green-700';
  $btnSuspend = $btn.' border-red-300 text-red-700 bg-white hover:bg-red-50';
  $tab = fn ($s) => route('admin.members.index', array_filter(['status' => $s, 'search' => request('search')]));
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <a href="{{ route('admin.dashboard') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }} rounded">Back to admin dashboard</a>
  <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Members management</h1>
  <p class="text-sm text-slate-500 mt-1">View member accounts, and suspend or restore access.</p>

  {{-- Status tabs --}}
  <nav class="mt-6 flex flex-wrap gap-2" aria-label="Filter by status">
    @foreach(['' => 'All'] + $statuses as $value => $label)
      @php $on = (string) $current === (string) $value; @endphp
      <a href="{{ $tab($value ?: null) }}" @if($on) aria-current="page" @endif
         class="px-3 py-1.5 rounded-full text-sm border {{ $on ? 'bg-[#101E33] border-[#101E33] text-white' : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300' }} {{ $focus }}">{{ $label }}</a>
    @endforeach
  </nav>

  {{-- Search --}}
  <form method="GET" action="{{ route('admin.members.index') }}" role="search" class="mt-4 mb-4 flex flex-wrap items-end gap-2">
    @if($current)<input type="hidden" name="status" value="{{ $current }}">@endif
    <div>
      <label for="search" class="block text-sm font-medium text-slate-700 mb-1">Search members</label>
      <input id="search" type="search" name="search" value="{{ request('search') }}" placeholder="Name or email" class="{{ $field }} w-72 max-w-full">
    </div>
    <button type="submit" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg font-medium hover:opacity-90 transition {{ $focus }}">Search</button>
    @if(request()->hasAny(['search', 'status']))
      <a href="{{ route('admin.members.index') }}" class="px-4 py-2 border border-slate-200 rounded-lg hover:bg-slate-50 {{ $focus }}">Clear</a>
    @endif
  </form>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
    <table class="w-full min-w-[1000px] text-sm">
      <caption class="sr-only">Members with email, role, location, profile details, join date and status</caption>
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th scope="col" class="text-left p-3 font-medium">Member</th>
          <th scope="col" class="text-left p-3 font-medium">Email</th>
          <th scope="col" class="text-left p-3 font-medium">Role</th>
          <th scope="col" class="text-left p-3 font-medium">Location</th>
          <th scope="col" class="text-left p-3 font-medium">Profile</th>
          <th scope="col" class="text-left p-3 font-medium">Joined</th>
          <th scope="col" class="text-left p-3 font-medium">Status</th>
          <th scope="col" class="text-left p-3 font-medium">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($members as $m)
          @php
            $mp = $m->profile;
            $place = implode(', ', array_filter([$mp?->city, $mp?->state, $mp?->country]));
            $role = $m->roles->first()?->name;
            $isSelf = auth()->id() === $m->id;
            $isProtected = $isSelf || $m->isSuperAdmin();
            $name = e(addslashes($m->name));
          @endphp
          <tr class="border-t border-slate-100 align-top hover:bg-slate-50/60">

            {{-- Member --}}
            <td class="p-3">
              <div class="flex items-start gap-3">
                @if($mp?->avatar)
                  <img src="{{ $mp->avatar }}" alt="" loading="lazy" class="w-10 h-10 shrink-0 rounded-full object-cover">
                @else
                  <div class="w-10 h-10 shrink-0 rounded-full bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center font-bold" aria-hidden="true">{{ strtoupper(substr($m->name,0,1)) }}</div>
                @endif
                <div class="min-w-0">
                  <a href="{{ route('admin.members.show', $m) }}" class="font-medium text-[#101E33] hover:text-[#FF5C3D] hover:underline {{ $focus }} rounded">{{ $m->name }}</a>
                  @if($isSelf)<span class="ml-1 text-[11px] text-slate-500">(you)</span>@endif
                  @if($mp?->username)<div class="text-xs text-slate-500">&#64;{{ $mp->username }}</div>@endif
                </div>
              </div>
            </td>

            <td class="p-3 text-slate-700 break-all">
              <a href="mailto:{{ $m->email }}" class="hover:text-[#FF5C3D] hover:underline {{ $focus }} rounded">{{ $m->email }}</a>
            </td>

            <td class="p-3 text-slate-700">{{ $role ? ucfirst(str_replace('_', ' ', $role)) : 'No role' }}</td>

            <td class="p-3 text-slate-700">{{ $place ?: 'Not set' }}</td>

            {{-- Profile details --}}
            <td class="p-3">
              <dl class="space-y-0.5 text-xs">
                <div class="flex gap-1"><dt class="text-slate-500">Title:</dt><dd class="text-slate-800">{{ $mp?->professional_title ?: 'Not set' }}</dd></div>
                <div class="flex gap-1"><dt class="text-slate-500">Level:</dt><dd class="text-slate-800">{{ $mp?->experience_level ?: 'Not set' }}</dd></div>
                <div class="flex gap-1">
                  <dt class="text-slate-500">Visibility:</dt>
                  <dd class="text-slate-800">{{ $mp ? ($mp->is_public ? 'Public' : 'Private') : 'No profile' }}</dd>
                </div>
              </dl>
            </td>

            {{-- Joined --}}
            <td class="p-3 text-xs text-slate-700 whitespace-nowrap">
              @if($m->created_at)
                <time datetime="{{ $m->created_at->toIso8601String() }}" title="{{ $m->created_at->format('M j, Y g:i A') }}">{{ $m->created_at->format('M j, Y') }}</time>
                <div class="text-slate-500">{{ $m->created_at->diffForHumans() }}</div>
              @endif
            </td>

            {{-- Status --}}
            <td class="p-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badge[$m->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($m->status) }}</span>
            </td>

            {{-- Actions --}}
            <td class="p-3">
              <div class="flex flex-wrap gap-2 max-w-[13rem]">
                <a href="{{ route('admin.members.show', $m) }}" class="{{ $btnView }}">Open<span class="sr-only"> {{ $m->name }}</span></a>

                @if($isProtected)
                  <span class="text-xs text-slate-500 self-center">{{ $isSelf ? 'Your account' : 'Super admin' }}</span>
                @elseif($m->status === 'suspended')
                  <form method="POST" action="{{ route('admin.members.restore', $m) }}" onsubmit="return confirm('Restore access for {{ $name }}?')">
                    @csrf
                    <button type="submit" class="{{ $btnRestore }}">Restore<span class="sr-only"> {{ $m->name }}</span></button>
                  </form>
                @else
                  <form method="POST" action="{{ route('admin.members.suspend', $m) }}" onsubmit="return confirm('Suspend {{ $name }}?')">
                    @csrf
                    <button type="submit" class="{{ $btnSuspend }}">Suspend<span class="sr-only"> {{ $m->name }}</span></button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="p-8 text-center text-slate-500">
            No members match your filters.
            @if(request()->hasAny(['search', 'status']))<a href="{{ route('admin.members.index') }}" class="text-[#FF5C3D] hover:underline">Clear filters</a>@endif
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <nav class="mt-4" aria-label="Pagination">{{ $members->withQueryString()->links() }}</nav>
</div>
@endsection