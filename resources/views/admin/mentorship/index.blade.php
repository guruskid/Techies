@extends('layouts.admin')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <div class="mb-6">
    <h1 class="font-display text-2xl font-extrabold text-[#101E33]">Mentorship</h1>
    <p class="mt-1 text-sm text-slate-500">Match mentors and mentees, then close cycles when they finish.</p>
  </div>

  <div class="grid lg:grid-cols-2 gap-6 items-start">
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
      <h2 class="font-display text-lg font-bold text-[#101E33]">Mentees waiting</h2>
      <div class="mt-4 space-y-3">
        @forelse($mentees as $mentee)
          <div class="rounded-lg border border-slate-100 p-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="font-medium text-[#101E33]">{{ $mentee->user?->name }}</p>
                <p class="text-xs text-slate-500">{{ $mentee->user?->email }}</p>
              </div>
              <span class="rounded-full px-2 py-0.5 text-xs {{ $mentee->isMatched() ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($mentee->status) }}</span>
            </div>
            @if($mentee->expertise)<p class="mt-2 text-sm text-slate-600">Focus: {{ $mentee->expertise }}</p>@endif
            @if($mentee->goals)<p class="mt-1 text-sm text-slate-600 whitespace-pre-line">{{ $mentee->goals }}</p>@endif
            @if(! $mentee->isMatched())
              <form method="POST" action="{{ route('admin.mentorship.match', $mentee) }}" class="mt-3 flex flex-col sm:flex-row gap-2">
                @csrf
                <select name="mentor_request_id" required class="min-w-0 flex-1 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                  <option value="">Choose a mentor</option>
                  @foreach($mentors->where('status', 'pending') as $mentor)
                    <option value="{{ $mentor->id }}">{{ $mentor->user?->name }} — {{ $mentor->expertise ?: 'General' }}</option>
                  @endforeach
                </select>
                <button class="rounded-lg bg-[#FF5C3D] px-4 py-2 text-sm font-semibold text-white">Match</button>
              </form>
            @else
              <p class="mt-2 text-sm text-green-700">Matched with {{ $mentee->match?->name }}</p>
            @endif
          </div>
        @empty
          <p class="text-sm text-slate-500">No mentee requests yet.</p>
        @endforelse
      </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
      <h2 class="font-display text-lg font-bold text-[#101E33]">Mentors available</h2>
      <div class="mt-4 space-y-3">
        @forelse($mentors as $mentor)
          <div class="rounded-lg border border-slate-100 p-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="font-medium text-[#101E33]">{{ $mentor->user?->name }}</p>
                <p class="text-xs text-slate-500">{{ $mentor->user?->email }}</p>
              </div>
              <span class="rounded-full px-2 py-0.5 text-xs {{ $mentor->isMatched() ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($mentor->status) }}</span>
            </div>
            @if($mentor->expertise)<p class="mt-2 text-sm text-slate-600">{{ $mentor->expertise }}</p>@endif
            @if($mentor->availability)<p class="text-xs text-slate-500">{{ $mentor->availability }}</p>@endif
          </div>
        @empty
          <p class="text-sm text-slate-500">No mentor requests yet.</p>
        @endforelse
      </div>
    </section>
  </div>

  <section class="mt-8 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <h2 class="font-display text-lg font-bold text-[#101E33]">Active matches</h2>
    <div class="mt-4 overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500">
          <tr><th class="p-3 text-left">Mentee</th><th class="p-3 text-left">Mentor</th><th class="p-3 text-left">Matched</th><th class="p-3"></th></tr>
        </thead>
        <tbody>
          @forelse($matched as $pair)
            <tr class="border-t border-slate-100">
              <td class="p-3 font-medium text-[#101E33]">{{ $pair->user?->name }}</td>
              <td class="p-3 text-slate-600">{{ $pair->match?->name }}</td>
              <td class="p-3 text-slate-600">{{ $pair->matched_at?->format('M j, Y') }}</td>
              <td class="p-3 text-right">
                <form method="POST" action="{{ route('admin.mentorship.close', $pair) }}" onsubmit="return confirm('Close this mentorship cycle?')">
                  @csrf
                  <button class="text-sm text-red-600 hover:underline">Close cycle</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="p-6 text-center text-slate-500">No active matches yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
</div>
@endsection