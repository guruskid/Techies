@extends('layouts.admin')
@section('content')
@php
  $current = request('status');
  $statuses = ['draft' => 'Draft', 'published' => 'Published', 'cancelled' => 'Cancelled'];
  $badge = [
    'draft'     => 'bg-amber-50 text-amber-700',
    'published' => 'bg-green-50 text-green-700',
    'cancelled' => 'bg-red-50 text-red-700',
  ];
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-1';
  $field = 'border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $btn = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold border transition '.$focus;
  $btnEdit   = $btn.' border-[#101E33] bg-[#101E33] text-white hover:opacity-90';
  $btnView   = $btn.' border-slate-200 text-slate-700 bg-white hover:bg-slate-50';
  $btnDelete = $btn.' border-red-300 text-red-700 bg-white hover:bg-red-50';
  $tab = fn ($s) => route('admin.events.index', array_filter([
    'status' => $s, 'search' => request('search'), 'category' => request('category'),
  ]));
  $user = auth()->user();
  $isStaff = $user->isAdmin() || $user->isSuperAdmin();
  $homeUrl = $isStaff ? route('admin.dashboard') : route('dashboard');
  $homeLabel = $isStaff ? 'Back to admin dashboard' : 'Back to my dashboard';
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
    <div>
      <a href="{{ $homeUrl }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }} rounded">{{ $homeLabel }}</a>
      <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Events</h1>
      <p class="mt-1 text-sm text-slate-500">
        {{ $isStaff ? 'Create and manage community events.' : 'Draft events for your community. An admin publishes them once reviewed.' }}
      </p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('admin.opportunities.index') }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 transition text-center {{ $focus }}">My opportunities</a>
      <a href="{{ route('admin.events.create') }}" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition text-center {{ $focus }}">New event</a>
    </div>
  </div>

  {{-- Status tabs (staff only — members submit for approval) --}}
  @if($isStaff)
  <nav class="flex flex-wrap gap-2" aria-label="Filter by status">
    @foreach(['' => 'All'] + $statuses as $value => $label)
      @php $on = (string) $current === (string) $value; @endphp
      <a href="{{ $tab($value ?: null) }}" @if($on) aria-current="page" @endif
         class="px-3 py-1.5 rounded-full text-sm border {{ $on ? 'bg-[#101E33] border-[#101E33] text-white' : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300' }} {{ $focus }}">{{ $label }}</a>
    @endforeach
  </nav>
  @endif

  {{-- Search and category --}}
  <form method="GET" action="{{ route('admin.events.index') }}" role="search" class="mt-4 mb-4 flex flex-wrap items-end gap-2">
    @if($current)<input type="hidden" name="status" value="{{ $current }}">@endif
    <div>
      <label for="search" class="block text-sm font-medium text-slate-700 mb-1">Search events</label>
      <input id="search" type="search" name="search" value="{{ request('search') }}" placeholder="Title, city or venue" class="{{ $field }} w-64 max-w-full">
    </div>
    <div>
      <label for="category" class="block text-sm font-medium text-slate-700 mb-1">Category</label>
      <select id="category" name="category" onchange="this.form.submit()" class="{{ $field }}">
        <option value="">All categories</option>
        @foreach($categories as $category)
          <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
        @endforeach
      </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-medium hover:opacity-90 transition {{ $focus }}">Search</button>
    @if(request()->hasAny(['search', 'status', 'category']))
      <a href="{{ route('admin.events.index') }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm hover:bg-slate-50 {{ $focus }}">Clear</a>
    @endif
  </form>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
    <table class="w-full min-w-[1000px] text-sm">
      <caption class="sr-only">Events with category, date, location, status and actions</caption>
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th scope="col" class="text-left p-3 font-medium">Event</th>
          <th scope="col" class="text-left p-3 font-medium">Category</th>
          <th scope="col" class="text-left p-3 font-medium">Date</th>
          <th scope="col" class="text-left p-3 font-medium">Location</th>
          <th scope="col" class="text-left p-3 font-medium">Status</th>
          <th scope="col" class="text-left p-3 font-medium">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($events as $event)
          @php
            $start = $event->start_date;
            $end = $event->end_date;
            $isPast = $start && $start->isPast() && (! $end || $end->isPast());
            $venue = $event->venue ?? null;
            $place = implode(', ', array_filter([$event->city, $event->state]));
            $name = e(addslashes($event->title));
          @endphp
          <tr class="border-t border-slate-100 align-top hover:bg-slate-50/60">

            {{-- Event --}}
            <td class="p-3">
              <a href="{{ route('admin.events.edit', $event) }}" class="font-medium text-[#101E33] hover:text-[#FF5C3D] hover:underline {{ $focus }} rounded">{{ $event->title }}</a>
              <div class="mt-0.5 text-xs text-slate-500 break-all">/events/{{ $event->slug }}</div>
              <div class="mt-1 text-xs text-slate-600">
                <a href="{{ route('admin.events.registrations', $event) }}" class="text-[#FF5C3D] hover:underline {{ $focus }} rounded">
                  {{ $event->registrations_count }} {{ \Illuminate\Support\Str::plural('registration', $event->registrations_count) }}
                  @if($event->waitlisted_count) · {{ $event->waitlisted_count }} waitlisted @endif
                  · manage
                </a>
              </div>
            </td>

            <td class="p-3 text-slate-700">{{ $event->category?->name ?? 'Uncategorised' }}</td>

            {{-- Date --}}
            <td class="p-3 whitespace-nowrap">
              @if($start)
                <time datetime="{{ $start->toIso8601String() }}" class="text-slate-800">{{ $start->format('M j, Y') }}</time>
                @if($end && ! $end->isSameDay($start))
                  <div class="text-xs text-slate-500">to {{ $end->format('M j, Y') }}</div>
                @endif
                <div class="mt-0.5 text-xs {{ $isPast ? 'text-slate-500' : 'text-[#C23A1F] font-medium' }}">{{ $isPast ? 'Past' : $start->diffForHumans() }}</div>
              @else
                <span class="text-slate-500">No date</span>
              @endif
            </td>

            {{-- Location --}}
            <td class="p-3 text-slate-700">
              @if($event->is_online)
                <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-700">Online</span>
              @elseif($venue || $place)
                @if($venue)<div>{{ $venue }}</div>@endif
                @if($place)<div class="text-xs text-slate-500">{{ $place }}</div>@endif
              @else
                <span class="text-slate-500">Not set</span>
              @endif
            </td>

            {{-- Status --}}
            <td class="p-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badge[$event->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $isStaff ? ucfirst($event->status) : ($event->status === 'draft' ? 'Awaiting review' : ucfirst($event->status)) }}</span>
            </td>

            {{-- Actions --}}
            <td class="p-3">
              <div class="flex flex-wrap gap-2 max-w-[15rem]">
                <a href="{{ route('admin.events.edit', $event) }}" class="{{ $btnEdit }}">Edit<span class="sr-only"> {{ $event->title }}</span></a>
                @if($event->status === 'published')
                  <a href="{{ route('events.show', $event) }}" class="{{ $btnView }}">View<span class="sr-only"> {{ $event->title }} on the site</span></a>
                @endif
                <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Delete {{ $name }}? This cannot be undone.')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="{{ $btnDelete }}">Delete<span class="sr-only"> {{ $event->title }}</span></button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="p-8 text-center text-slate-500">
            @if(request()->hasAny(['search', 'status', 'category']))
              No events match your filters. <a href="{{ route('admin.events.index') }}" class="text-[#FF5C3D] hover:underline">Clear filters</a>
            @else
              No events yet. <a href="{{ route('admin.events.create') }}" class="text-[#FF5C3D] hover:underline">Create the first event</a>
            @endif
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <nav class="mt-4" aria-label="Pagination">{{ $events->withQueryString()->links() }}</nav>
</div>
@endsection