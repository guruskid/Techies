@extends('layouts.app')
@section('title', 'Member grants · Meet with the Techies')
@section('content')
@include('partials.member-nav')
<div class="max-w-6xl mx-auto px-4 py-8 pb-14">
  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#C23A1F]">Fund your work</p>
  <h1 class="mt-2 font-display text-2xl md:text-3xl font-extrabold text-[#101E33]">Micro-grants</h1>
  <p class="mt-1 text-sm text-slate-500">Small grants for member projects that strengthen the community.</p>
  <div class="mt-6 grid md:grid-cols-2 gap-4">
    @forelse($grants as $grant)
      <a href="{{ route('grants.show', $grant->slug) }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-[#FF5C3D]/40">
        <div class="flex items-start justify-between gap-3">
          <h2 class="font-display text-xl font-bold text-[#101E33]">{{ $grant->title }}</h2>
          <span class="rounded-full px-2 py-1 text-xs {{ $grant->status === 'open' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($grant->status) }}</span>
        </div>
        <p class="mt-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags((string) $grant->description), 150) }}</p>
        <p class="mt-4 text-xs text-slate-500">{{ $grant->amount ? '₦'.number_format($grant->amount) : 'Amount to be confirmed' }} · closes {{ $grant->closes_at?->format('M j, Y') ?? 'when filled' }}</p>
      </a>
    @empty
      <p class="text-slate-500">No grants open right now.</p>
    @endforelse
  </div>
  <div class="mt-4">{{ $grants->links() }}</div>
</div>
@endsection