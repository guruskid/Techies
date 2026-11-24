@extends('layouts.admin')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <div class="mb-6"><h1 class="font-display text-2xl font-extrabold text-[#101E33]">Referrals</h1><p class="mt-1 text-sm text-slate-500">Member referral offers and requests.</p></div>
  <div class="grid lg:grid-cols-2 gap-6 items-start">
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
      <h2 class="font-display text-lg font-bold text-[#101E33]">Offers</h2>
      @forelse($offers as $offer)
        <div class="mt-3 rounded-lg border border-slate-100 p-3 text-sm">
          <p class="font-medium text-[#101E33]">{{ $offer->user?->name }} — {{ $offer->opportunity?->title }}</p>
          <p class="text-xs text-slate-500">{{ $offer->note }}</p>
        </div>
      @empty
        <p class="mt-2 text-sm text-slate-500">No referral offers yet.</p>
      @endforelse
      <div class="mt-4">{{ $offers->links() }}</div>
    </section>
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
      <h2 class="font-display text-lg font-bold text-[#101E33]">Requests</h2>
      @forelse($requests as $request)
        <div class="mt-3 rounded-lg border border-slate-100 p-3 text-sm">
          <p class="font-medium text-[#101E33]">{{ $request->requester?->name }} → {{ $request->offer?->user?->name }}</p>
          <p class="text-xs text-slate-500">{{ $request->offer?->opportunity?->title }}</p>
          <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs">{{ ucfirst($request->status) }}</span>
        </div>
      @empty
        <p class="mt-2 text-sm text-slate-500">No referral requests yet.</p>
      @endforelse
      <div class="mt-4">{{ $requests->links() }}</div>
    </section>
  </div>
</div>
@endsection