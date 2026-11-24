@extends('layouts.app')
@section('title', 'Member passport · Meet with the Techies')
@section('content')
@include('partials.member-nav')
@php
  $profile = $user->profile;
  $completion = $profile?->completionPercentage() ?? 0;
  $completeSteps = collect($steps)->where('done', true)->count();
  $referralUrl = route('register', ['ref' => $user->referral_code]);
  $qrData = $profile?->username ? $profile->publicUrl() : $referralUrl;
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 pb-14">
  <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
      <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#C23A1F]">Your membership</p>
      <h1 class="mt-2 font-display text-2xl md:text-3xl font-extrabold text-[#101E33]">Member passport</h1>
      <p class="mt-1 text-sm text-slate-500">Your identity, activity and progress in one place.</p>
    </div>
    @if($user->is_founding_member)
      <span class="inline-flex items-center gap-2 self-start rounded-full border border-[#FF5C3D]/30 bg-[#FF5C3D]/10 px-3 py-1.5 text-xs font-semibold text-[#C23A1F]">
        <span class="h-1.5 w-1.5 rounded-full bg-[#FF5C3D]"></span> Founding member
      </span>
    @endif
  </div>

  <div class="grid lg:grid-cols-[0.9fr_1.1fr] gap-6 items-start">
    <div class="space-y-6">
      {{-- Digital card --}}
      <section class="relative overflow-hidden rounded-2xl bg-[#101E33] p-6 text-white shadow-xl" aria-labelledby="card-h">
        <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-[#FF5C3D]/20 blur-3xl" aria-hidden="true"></div>
        <div class="relative">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Meet with the Techies</p>
              <h2 id="card-h" class="mt-2 font-display text-xl font-bold">{{ $user->name }}</h2>
              <p class="mt-1 text-sm text-slate-300">{{ $profile?->professional_title ?: 'Community member' }}</p>
            </div>
            @if($profile?->avatar)
              <img src="{{ $profile->avatar }}" alt="" class="h-14 w-14 rounded-full border-2 border-white/20 object-cover">
            @else
              <span class="flex h-14 w-14 items-center justify-center rounded-full border-2 border-white/20 bg-white/10 font-display text-xl font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            @endif
          </div>

          <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-[10px] uppercase tracking-wider text-slate-400">Member number</p>
              <p class="mt-1 font-semibold">{{ $user->member_number ?: 'Pending' }}</p>
            </div>
            <div>
              <p class="text-[10px] uppercase tracking-wider text-slate-400">Member since</p>
              <p class="mt-1 font-semibold">{{ $user->membership_paid_at?->format('M Y') ?? 'Pending' }}</p>
            </div>
          </div>

          <div class="mt-6 flex items-end justify-between gap-4">
            <div>
              <p class="text-[10px] uppercase tracking-wider text-slate-400">Status</p>
              <p class="mt-1 inline-flex rounded-full bg-white/10 px-2.5 py-1 text-xs font-semibold">{{ $user->hasActiveMembership() ? 'Active member' : ucfirst($user->membership_status) }}</p>
            </div>
            <div id="member-qr" class="rounded-lg bg-white p-2" aria-label="Member QR code"></div>
          </div>
        </div>
      </section>

      {{-- Referral --}}
      <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="referral-h">
        <h2 id="referral-h" class="font-display text-lg font-bold text-[#101E33]">Invite someone in</h2>
        <p class="mt-1 text-sm text-slate-500">Share your referral link. You have referred {{ $stats['referrals'] }} {{ \Illuminate\Support\Str::plural('person', $stats['referrals']) }}.</p>
        <div class="mt-4 flex flex-col sm:flex-row gap-2" x-data="{ copied: false }">
          <input value="{{ $referralUrl }}" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">
          <button type="button" @click="navigator.clipboard.writeText('{{ $referralUrl }}'); copied = true; setTimeout(() => copied = false, 1600)" class="shrink-0 rounded-lg bg-[#101E33] px-4 py-2 text-sm font-semibold text-white">
            <span x-text="copied ? 'Copied' : 'Copy'">Copy</span>
          </button>
        </div>
        <p class="mt-2 text-xs text-slate-500">Code: <span class="font-mono font-semibold text-[#101E33]">{{ $user->referral_code }}</span></p>
      </section>
    </div>

    <div class="space-y-6">
      {{-- Stats --}}
      <section class="grid grid-cols-2 sm:grid-cols-3 gap-3" aria-label="Your activity">
        @foreach([
          ['Events', $stats['events']],
          ['Talks', $stats['talks']],
          ['Articles', $stats['articles']],
          ['Chapters', $stats['chapters']],
          ['Certificates', $stats['certificates']],
          ['Referrals', $stats['referrals']],
        ] as [$label, $value])
          <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">{{ $label }}</p>
            <p class="mt-1 font-display text-2xl font-extrabold text-[#101E33]">{{ $value }}</p>
          </div>
        @endforeach
      </section>

      {{-- Onboarding --}}
      <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="steps-h">
        <div class="flex items-center justify-between gap-4">
          <div>
            <h2 id="steps-h" class="font-display text-lg font-bold text-[#101E33]">Your next steps</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $completeSteps }} of {{ count($steps) }} complete</p>
          </div>
          <span class="text-2xl font-extrabold text-[#C23A1F]">{{ round(($completeSteps / count($steps)) * 100) }}%</span>
        </div>
        <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
          <div class="h-full rounded-full bg-[#FF5C3D]" style="width: {{ round(($completeSteps / count($steps)) * 100) }}%"></div>
        </div>
        <ul class="mt-5 space-y-2">
          @foreach($steps as $step)
            <li>
              <a href="{{ $step['url'] }}" class="flex items-center gap-3 rounded-lg border border-slate-100 px-3 py-2.5 text-sm transition hover:border-slate-200 hover:bg-slate-50">
                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border {{ $step['done'] ? 'border-green-600 bg-green-600 text-white' : 'border-slate-300 text-transparent' }}">
                  <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                </span>
                <span class="{{ $step['done'] ? 'text-slate-500 line-through' : 'font-medium text-[#101E33]' }}">{{ $step['label'] }}</span>
              </a>
            </li>
          @endforeach
        </ul>
      </section>

      {{-- Mentorship --}}
      <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="mentor-h">
        <div class="flex items-center justify-between gap-3">
          <h2 id="mentor-h" class="font-display text-lg font-bold text-[#101E33]">Mentorship</h2>
          <a href="{{ route('mentorship.index') }}" class="text-sm font-semibold text-[#C23A1F] hover:underline">Manage</a>
        </div>
        @if($user->mentorshipRequest?->isMatched())
          <p class="mt-2 text-sm text-slate-600">You are matched with <strong class="text-[#101E33]">{{ $user->mentorshipRequest->match?->name }}</strong>.</p>
          <p class="mt-1 text-xs text-slate-500">Matched {{ $user->mentorshipRequest->matched_at?->format('M j, Y') }}.</p>
        @elseif($user->mentorshipRequest)
          <p class="mt-2 text-sm text-slate-600">Your {{ $user->mentorshipRequest->role }} request is pending. We will email you when a match is ready.</p>
        @else
          <p class="mt-2 text-sm text-slate-600">Request a mentor or volunteer as one. Matches run in three-month cycles.</p>
          <a href="{{ route('mentorship.index') }}" class="mt-3 inline-flex rounded-lg bg-[#101E33] px-4 py-2 text-sm font-semibold text-white">Join mentorship</a>
        @endif
      </section>

      {{-- Certificates --}}
      <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="certs-h">
        <h2 id="certs-h" class="font-display text-lg font-bold text-[#101E33]">Certificates</h2>
        @if($user->certificates->isEmpty())
          <p class="mt-2 text-sm text-slate-500">Certificates for events, speaking, volunteering and mentorship will appear here.</p>
        @else
          <ul class="mt-3 divide-y divide-slate-100">
            @foreach($user->certificates as $certificate)
              <li class="py-3">
                <a href="{{ route('certificates.show', $certificate->certificate_number) }}" class="font-medium text-[#101E33] hover:text-[#C23A1F]">{{ $certificate->title }}</a>
                <p class="text-xs text-slate-500">{{ \App\Models\Certificate::TYPES[$certificate->type] ?? ucfirst($certificate->type) }} · {{ $certificate->issued_at?->format('M j, Y') }}</p>
              </li>
            @endforeach
          </ul>
        @endif
      </section>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
  (function () {
    var el = document.getElementById('member-qr');
    if (!el || typeof QRCode === 'undefined') return;
    new QRCode(el, { text: @json($qrData), width: 92, height: 92, correctLevel: QRCode.CorrectLevel.M });
  })();
</script>
@endsection