@extends('layouts.app')
@section('title', 'Referrals · Meet with the Techies')
@section('content')
@include('partials.member-nav')
<div class="max-w-6xl mx-auto px-4 py-8 pb-14">
  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#C23A1F]">Warm paths in</p>
  <h1 class="mt-2 font-display text-2xl md:text-3xl font-extrabold text-[#101E33]">Job referrals</h1>
  <p class="mt-1 text-sm text-slate-500">Offer referrals for roles you can vouch for, and request referrals from members who can help.</p>

  <div class="mt-6 grid lg:grid-cols-2 gap-6 items-start">
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
      <h2 class="font-display text-lg font-bold text-[#101E33]">Your offers</h2>
      @forelse($offers as $offer)
        <div class="mt-4 rounded-lg border border-slate-100 p-3 text-sm">
          <p class="font-medium text-[#101E33]">{{ $offer->opportunity?->title }}</p>
          <p class="text-xs text-slate-500">{{ $offer->note ?: 'Offering a referral' }}</p>
          @foreach($offer->requests as $request)
            <div class="mt-2 border-t border-slate-100 pt-2">
              <p class="text-xs text-slate-600">{{ $request->requester?->name }}: {{ $request->message }}</p>
              @if($request->status === 'pending')
                <form method="POST" action="{{ route('referrals.respond', $request) }}" class="mt-2 flex gap-2">@csrf
                  <button name="status" value="accepted" class="rounded bg-green-600 px-3 py-1 text-xs font-semibold text-white">Accept</button>
                  <button name="status" value="declined" class="rounded border border-slate-200 px-3 py-1 text-xs">Decline</button>
                </form>
              @else
                <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs {{ $request->status === 'accepted' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($request->status) }}</span>
              @endif
            </div>
          @endforeach
        </div>
      @empty
        <p class="mt-2 text-sm text-slate-500">You are not offering referrals yet. Add one from any opportunity page.</p>
      @endforelse
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
      <h2 class="font-display text-lg font-bold text-[#101E33]">Your requests</h2>
      @forelse($requests as $request)
        <div class="mt-4 rounded-lg border border-slate-100 p-3 text-sm">
          <p class="font-medium text-[#101E33]">{{ $request->offer?->opportunity?->title }}</p>
          <p class="text-xs text-slate-500">Referrer: {{ $request->offer?->user?->name }}</p>
          <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs {{ $request->status === 'accepted' ? 'bg-green-50 text-green-700' : ($request->status === 'declined' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">{{ ucfirst($request->status) }}</span>
        </div>
      @empty
        <p class="mt-2 text-sm text-slate-500">No referral requests yet.</p>
      @endforelse
    </section>
  </div>
</div>
@endsection