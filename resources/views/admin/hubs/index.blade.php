@extends('layouts.app')
@section('content')
@php
  $statuses = ['pending', 'approved', 'rejected', 'suspended'];
  $current = request('status');
  $badge = [
    'pending'   => 'bg-amber-50 text-amber-700',
    'approved'  => 'bg-green-50 text-green-700',
    'rejected'  => 'bg-red-50 text-red-700',
    'suspended' => 'bg-slate-200 text-slate-700',
  ];
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-1';
  $field = 'border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $safeUrl = function ($u) {
    $u = trim((string) $u);
    if ($u === '') return null;
    if (preg_match('#^https?://#i', $u)) return $u;
    if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $u)) return null;
    return 'https://'.ltrim($u, '/');
  };
  $btn = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold border transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-1';
  $btnView    = $btn.' border-slate-200 text-slate-700 bg-white hover:bg-slate-50';
  $btnApprove = $btn.' border-green-600 bg-green-600 text-white hover:bg-green-700';
  $btnRestore = $btn.' border-green-600 text-green-700 bg-white hover:bg-green-50';
  $btnReject  = $btn.' border-red-300 text-red-700 bg-white hover:bg-red-50';
  $btnSuspend = $btn.' border-amber-400 text-amber-800 bg-white hover:bg-amber-50';
  $tab = fn ($s) => route('admin.hubs.index', array_filter(['status' => $s, 'search' => request('search')]));
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <a href="{{ route('admin.dashboard') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }} rounded">Back to admin dashboard</a>
  <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Hubs management</h1>
  <p class="text-sm text-slate-500 mt-1">Review, approve and manage technology hubs.</p>

  {{-- Status tabs --}}
  <nav class="mt-6 flex flex-wrap gap-2" aria-label="Filter by status">
    @foreach([null => 'All'] + array_combine($statuses, array_map('ucfirst', $statuses)) as $value => $label)
      @php $on = ($current ?: null) === ($value ?: null); @endphp
      <a href="{{ $tab($value) }}" @if($on) aria-current="page" @endif
         class="px-3 py-1.5 rounded-full text-sm border {{ $on ? 'bg-[#101E33] border-[#101E33] text-white' : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300' }} {{ $focus }}">{{ $label }}</a>
    @endforeach
  </nav>

  {{-- Search --}}
  <form method="GET" action="{{ route('admin.hubs.index') }}" role="search" class="mt-4 mb-4 flex flex-wrap items-end gap-2">
    @if($current)<input type="hidden" name="status" value="{{ $current }}">@endif
    <div>
      <label for="search" class="block text-sm font-medium text-slate-700 mb-1">Search hubs</label>
      <input id="search" type="search" name="search" value="{{ request('search') }}" placeholder="Hub name" class="{{ $field }} w-64 max-w-full">
    </div>
    <button type="submit" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg font-medium hover:opacity-90 transition {{ $focus }}">Search</button>
    @if(request()->hasAny(['search', 'status']))
      <a href="{{ route('admin.hubs.index') }}" class="px-4 py-2 border border-slate-200 rounded-lg hover:bg-slate-50 {{ $focus }}">Clear</a>
    @endif
  </form>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
    <table class="w-full min-w-[1100px] text-sm">
      <caption class="sr-only">Hubs with owner, location, details, contact, submission date and status</caption>
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th scope="col" class="text-left p-3 font-medium">Hub</th>
          <th scope="col" class="text-left p-3 font-medium">Owner</th>
          <th scope="col" class="text-left p-3 font-medium">Location</th>
          <th scope="col" class="text-left p-3 font-medium">Profile</th>
          <th scope="col" class="text-left p-3 font-medium">Contact</th>
          <th scope="col" class="text-left p-3 font-medium">Submitted</th>
          <th scope="col" class="text-left p-3 font-medium">Status</th>
          <th scope="col" class="text-left p-3 font-medium">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($hubs as $h)
          @php
            $place = implode(', ', array_filter([$h->city, $h->state, $h->country]));
            $website = $safeUrl($h->website);
          @endphp
          <tr class="border-t border-slate-100 align-top hover:bg-slate-50/60">

            {{-- Hub --}}
            <td class="p-3">
              <div class="flex items-start gap-3">
                @if($h->logo)
                  <img src="{{ $h->logo }}" alt="" loading="lazy" class="w-10 h-10 shrink-0 rounded-lg object-cover">
                @else
                  <div class="w-10 h-10 shrink-0 rounded-lg bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center font-bold" aria-hidden="true">{{ strtoupper(substr($h->name,0,1)) }}</div>
                @endif
                <div class="min-w-0">
                  <a href="{{ route('admin.hubs.show', $h) }}" class="font-medium text-[#101E33] hover:text-[#FF5C3D] hover:underline {{ $focus }} rounded">{{ $h->name }}</a>
                  <div class="mt-1 flex flex-wrap items-center gap-2">
                    @if($h->is_verified)
                      <span class="px-1.5 py-0.5 rounded bg-green-50 text-green-700 text-[11px] font-medium">Verified</span>
                    @endif
                    @if($website)
                      <a href="{{ $website }}" target="_blank" rel="noopener noreferrer" class="text-xs text-[#FF5C3D] hover:underline {{ $focus }} rounded">Website<span class="sr-only"> for {{ $h->name }} (opens in a new tab)</span></a>
                    @endif
                  </div>
                </div>
              </div>
            </td>

            {{-- Owner --}}
            <td class="p-3">
              @if($h->owner)
                <div class="text-slate-800">{{ $h->owner->name }}</div>
                <div class="text-xs text-slate-500 break-all">{{ $h->owner->email }}</div>
              @else
                <span class="text-slate-500">No owner</span>
              @endif
            </td>

            {{-- Location --}}
            <td class="p-3 text-slate-700">{{ $place ?: 'Not set' }}</td>

            {{-- Profile details --}}
            <td class="p-3">
              <dl class="space-y-0.5 text-xs">
                <div class="flex gap-1"><dt class="text-slate-500">Type:</dt><dd class="text-slate-800">{{ $h->organization_type ?: 'Not set' }}</dd></div>
                <div class="flex gap-1"><dt class="text-slate-500">Community:</dt><dd class="text-slate-800">{{ $h->community_size ?: 'Not set' }}</dd></div>
                <div class="flex gap-1"><dt class="text-slate-500">Founded:</dt><dd class="text-slate-800">{{ $h->year_established ?: 'Not set' }}</dd></div>
                @if(isset($h->events_count))
                  <div class="flex gap-1"><dt class="text-slate-500">Events:</dt><dd class="text-slate-800">{{ $h->events_count }}</dd></div>
                @endif
              </dl>
            </td>

            {{-- Contact --}}
            <td class="p-3 text-xs">
              @if($h->email)<div><a href="mailto:{{ $h->email }}" class="text-slate-800 hover:text-[#FF5C3D] hover:underline break-all {{ $focus }} rounded">{{ $h->email }}</a></div>@endif
              @if($h->phone)<div class="mt-0.5"><a href="tel:{{ preg_replace('/[^\d+]/', '', $h->phone) }}" class="text-slate-800 hover:text-[#FF5C3D] hover:underline {{ $focus }} rounded">{{ $h->phone }}</a></div>@endif
              @unless($h->email || $h->phone)<span class="text-slate-500">No contact details</span>@endunless
            </td>

            {{-- Submitted --}}
            <td class="p-3 text-xs text-slate-700 whitespace-nowrap">
              @if($h->created_at)
                <time datetime="{{ $h->created_at->toIso8601String() }}" title="{{ $h->created_at->format('M j, Y g:i A') }}">{{ $h->created_at->format('M j, Y') }}</time>
                <div class="text-slate-500">{{ $h->created_at->diffForHumans() }}</div>
              @endif
            </td>

            {{-- Status --}}
            <td class="p-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badge[$h->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($h->status) }}</span>
            </td>

            {{-- Actions: only the moves that make sense for the hub's current status --}}
            <td class="p-3">
              <div class="flex flex-wrap gap-2 max-w-[15rem]">
                <a href="{{ route('admin.hubs.show', $h) }}" class="{{ $btnView }}">Open<span class="sr-only"> {{ $h->name }}</span></a>

                @if($h->status === 'pending')
                  <form method="POST" action="{{ route('admin.hubs.approve', $h) }}" onsubmit="return confirm('Approve {{ e(addslashes($h->name)) }}?')">
                    @csrf
                    <button type="submit" class="{{ $btnApprove }}">Approve<span class="sr-only"> {{ $h->name }}</span></button>
                  </form>
                  <form method="POST" action="{{ route('admin.hubs.reject', $h) }}" onsubmit="return confirm('Reject {{ e(addslashes($h->name)) }}?')">
                    @csrf
                    <button type="submit" class="{{ $btnReject }}">Reject<span class="sr-only"> {{ $h->name }}</span></button>
                  </form>

                @elseif($h->status === 'approved')
                  <form method="POST" action="{{ route('admin.hubs.suspend', $h) }}" onsubmit="return confirm('Suspend {{ e(addslashes($h->name)) }}?')">
                    @csrf
                    <button type="submit" class="{{ $btnSuspend }}">Suspend<span class="sr-only"> {{ $h->name }}</span></button>
                  </form>

                @elseif($h->status === 'suspended')
                  <form method="POST" action="{{ route('admin.hubs.approve', $h) }}" onsubmit="return confirm('Reinstate {{ e(addslashes($h->name)) }}?')">
                    @csrf
                    <button type="submit" class="{{ $btnRestore }}">Reinstate<span class="sr-only"> {{ $h->name }}</span></button>
                  </form>

                @elseif($h->status === 'rejected')
                  <form method="POST" action="{{ route('admin.hubs.approve', $h) }}" onsubmit="return confirm('Approve {{ e(addslashes($h->name)) }} after all?')">
                    @csrf
                    <button type="submit" class="{{ $btnRestore }}">Approve anyway<span class="sr-only"> {{ $h->name }}</span></button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="p-8 text-center text-slate-500">
            No hubs match your filters.
            @if(request()->hasAny(['search', 'status']))<a href="{{ route('admin.hubs.index') }}" class="text-[#FF5C3D] hover:underline">Clear filters</a>@endif
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <nav class="mt-4" aria-label="Pagination">{{ $hubs->withQueryString()->links() }}</nav>
</div>
@endsection