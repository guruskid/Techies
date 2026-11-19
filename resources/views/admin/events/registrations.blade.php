@extends('layouts.app')
@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-1';
  $field = 'border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $tabs = [
    '' => 'All',
    'registered' => 'Registered',
    'waitlisted' => 'Waitlisted',
    'cancelled' => 'Cancelled',
  ];
  $badge = [
    'registered' => 'bg-green-50 text-green-700',
    'waitlisted' => 'bg-amber-50 text-amber-800',
    'cancelled' => 'bg-slate-100 text-slate-600',
  ];
  $tabUrl = fn ($value) => route('admin.events.registrations', array_filter([
    'event' => $event,
    'status' => $value ?: null,
    'search' => $search ?: null,
  ]));
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
    <div>
      <a href="{{ route('admin.events.index') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }} rounded">Back to events</a>
      <h1 class="mt-2 text-2xl font-bold text-[#101E33]">{{ $event->title }}</h1>
      <p class="mt-1 text-sm text-slate-500">
        {{ $event->start_date->format('D, M j, Y') }}@if($event->start_time) at {{ $event->start_time }}@endif
        · <a href="{{ route('admin.events.edit', $event) }}" class="text-[#FF5C3D] hover:underline {{ $focus }} rounded">Edit event</a>
      </p>
    </div>
    <a href="{{ route('admin.events.registrations.export', $event) }}"
       class="px-4 py-2 bg-[#101E33] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition text-center {{ $focus }}">
      Export CSV<span class="sr-only"> of all attendees for {{ $event->title }}</span>
    </a>
  </div>

  {{-- Summary --}}
  <dl class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    @foreach([
      ['Registered', $counts['registered'], 'text-green-700 bg-green-50'],
      ['Waitlisted', $counts['waitlisted'], 'text-amber-800 bg-amber-50'],
      ['Checked in', $counts['checked_in'], 'text-[#101E33] bg-slate-100'],
      ['Cancelled', $counts['cancelled'], 'text-slate-600 bg-slate-50'],
    ] as [$label, $value, $classes])
      <div class="rounded-xl border border-slate-100 bg-white p-4">
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</dt>
        <dd class="mt-1 text-2xl font-bold {{ $classes }} inline-block px-2 rounded-lg">{{ $value }}</dd>
      </div>
    @endforeach
  </dl>

  {{-- Filters --}}
  <nav class="flex flex-wrap gap-2" aria-label="Filter attendees by status">
    @foreach($tabs as $value => $label)
      @php $on = (string) $status === (string) $value; @endphp
      <a href="{{ $tabUrl($value) }}" @if($on) aria-current="page" @endif
         class="px-3 py-1.5 rounded-full text-sm border {{ $on ? 'bg-[#101E33] border-[#101E33] text-white' : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300' }} {{ $focus }}">{{ $label }}</a>
    @endforeach
  </nav>

  <form method="GET" action="{{ route('admin.events.registrations', $event) }}" role="search" class="mt-4 flex flex-wrap items-end gap-2">
    @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
    <div>
      <label for="search" class="block text-sm font-medium text-slate-700 mb-1">Search attendees</label>
      <input id="search" type="search" name="search" value="{{ $search }}" placeholder="Name or email" class="{{ $field }} w-64 max-w-full">
    </div>
    <button type="submit" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-medium hover:opacity-90 transition {{ $focus }}">Search</button>
    @if($search !== '' || $status)
      <a href="{{ route('admin.events.registrations', $event) }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm hover:bg-slate-50 {{ $focus }}">Clear</a>
    @endif
  </form>

  <div class="mt-4 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
    <table class="w-full min-w-[820px] text-sm">
      <caption class="sr-only">Attendees for {{ $event->title }} with status and check-in controls</caption>
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th scope="col" class="text-left p-3 font-medium">Attendee</th>
          <th scope="col" class="text-left p-3 font-medium">Status</th>
          @if($event->requiresPayment())
            <th scope="col" class="text-left p-3 font-medium">Payment</th>
          @endif
          <th scope="col" class="text-left p-3 font-medium">Registered</th>
          <th scope="col" class="text-left p-3 font-medium">Check-in</th>
        </tr>
      </thead>
      <tbody>
        @forelse($registrations as $registration)
          <tr class="border-t border-slate-100 align-top hover:bg-slate-50/60">
            <td class="p-3">
              <div class="font-medium text-[#101E33]">{{ $registration->user?->name ?? 'Deleted member' }}</div>
              <div class="text-xs text-slate-500 break-all">{{ $registration->user?->email }}</div>
              @if($registration->user?->profile?->phone)
                <div class="text-xs text-slate-500">{{ $registration->user->profile->phone }}</div>
              @endif
            </td>
            <td class="p-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badge[$registration->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($registration->status) }}</span>
              @if($registration->waitlisted_at)
                <div class="mt-1 text-xs text-slate-500">Waitlisted {{ $registration->waitlisted_at->format('M j, Y') }}</div>
              @endif
            </td>
            @if($event->requiresPayment())
              <td class="p-3">
                @php
                  $paymentClasses = [
                    'not_required' => 'bg-slate-100 text-slate-600',
                    'unpaid' => 'bg-amber-50 text-amber-800',
                    'paid' => 'bg-green-50 text-green-700',
                    'refunded' => 'bg-slate-100 text-slate-600',
                  ];
                @endphp
                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $paymentClasses[$registration->payment_status] ?? 'bg-slate-100 text-slate-700' }}">
                  {{ \App\Models\EventRegistration::paymentStatuses()[$registration->payment_status] ?? ucfirst($registration->payment_status) }}
                </span>
                @if($registration->paid_at)
                  <div class="mt-1 text-xs text-slate-500">{{ $registration->paid_at->format('M j, Y') }}</div>
                @endif
                <form method="POST" action="{{ route('admin.events.registrations.payment', [$event, $registration]) }}" class="mt-2 flex flex-wrap items-center gap-1">
                  @csrf
                  <label class="sr-only" for="payment-status-{{ $registration->id }}">Payment status</label>
                  <select id="payment-status-{{ $registration->id }}" name="payment_status" class="border border-slate-200 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
                    @foreach(\App\Models\EventRegistration::paymentStatuses() as $value => $label)
                      <option value="{{ $value }}" @selected($registration->payment_status === $value)>{{ $label }}</option>
                    @endforeach
                  </select>
                  <label class="sr-only" for="payment-reference-{{ $registration->id }}">Payment reference</label>
                  <input id="payment-reference-{{ $registration->id }}" name="payment_reference" value="{{ $registration->payment_reference }}" placeholder="Reference" class="w-28 border border-slate-200 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
                  <button class="px-2 py-1 bg-[#101E33] text-white rounded-lg text-xs font-semibold hover:opacity-90 {{ $focus }}">Save</button>
                </form>
              </td>
            @endif
            <td class="p-3 whitespace-nowrap text-slate-700">
              {{ $registration->created_at?->format('M j, Y') ?? '—' }}
              <div class="text-xs text-slate-500">{{ $registration->created_at?->format('H:i') }}</div>
            </td>
            <td class="p-3">
              @if($registration->status === 'registered')
                <form method="POST" action="{{ route('admin.events.registrations.check-in', [$event, $registration]) }}">
                  @csrf
                  <button class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition {{ $focus }} {{ $registration->checked_in_at ? 'border-green-300 bg-green-50 text-green-700 hover:bg-green-100' : 'border-[#101E33] bg-[#101E33] text-white hover:opacity-90' }}">
                    {{ $registration->checked_in_at ? 'Checked in · undo' : 'Check in' }}
                  </button>
                </form>
                @if($registration->checked_in_at)
                  <div class="mt-1 text-xs text-slate-500">{{ $registration->checked_in_at->format('M j, H:i') }}</div>
                @endif
              @else
                <span class="text-xs text-slate-500">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="{{ $event->requiresPayment() ? 5 : 4 }}" class="p-8 text-center text-slate-500">
            @if($search !== '' || $status)
              No attendees match your filters. <a href="{{ route('admin.events.registrations', $event) }}" class="text-[#FF5C3D] hover:underline">Clear filters</a>
            @else
              Nobody has registered for this event yet.
            @endif
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <nav class="mt-4" aria-label="Pagination">{{ $registrations->links() }}</nav>
</div>
@endsection
