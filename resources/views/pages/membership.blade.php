@extends('layouts.app')
@section('title', 'Membership · Meet with the Techies')
@section('meta_description', 'Join Meet with the Techies with a one-time membership fee and get access to events, mentorship, opportunities, speaker calls and a member profile.')

@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
  $price = $currency === 'NGN' ? '₦'.number_format($fee) : $currency.' '.number_format($fee);
  $status = $user?->membership_status;
@endphp

<section class="relative overflow-hidden aurora text-white py-14 md:py-20">
  <div class="absolute inset-0 tech-grid opacity-30" aria-hidden="true"></div>
  <div class="relative max-w-6xl mx-auto px-4 grid lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)] gap-10 items-center">
    <div>
      <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#FF8A73]">One-time membership</p>
      <h1 class="mt-2 font-display text-3xl md:text-5xl font-bold leading-tight">Join a community that helps you grow.</h1>
      <p class="mt-4 max-w-2xl text-slate-300 text-lg">Pay once, belong for life. Meet with the Techies connects you to events, mentorship, opportunities, speaker stages and a network of people building across Nigeria.</p>
      <div class="mt-6 flex flex-wrap items-center gap-3">
        <span class="px-4 py-2 rounded-full bg-white/10 border border-white/15 text-sm">{{ $price }} once</span>
        <span class="px-4 py-2 rounded-full bg-white/10 border border-white/15 text-sm">No monthly renewal</span>
      </div>
    </div>

    <div class="bg-white text-[#101E33] rounded-3xl p-6 sm:p-8 shadow-2xl">
      <p class="text-sm font-semibold text-[#C23A1F]">Standard membership</p>
      <div class="mt-2 flex items-end gap-2">
        <span class="text-4xl font-bold">{{ $price }}</span>
        <span class="pb-1 text-sm text-slate-500">one time</span>
      </div>
      <p class="mt-2 text-sm text-slate-600">Your membership stays active. There is no yearly renewal.</p>

      @if(! $user)
        <a href="{{ route('register') }}" class="mt-6 block w-full text-center px-5 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition {{ $focus }}">Create an account</a>
        <a href="{{ route('login') }}" class="mt-3 block w-full text-center px-5 py-3 border border-slate-200 rounded-lg font-semibold hover:bg-slate-50 transition {{ $focus }}">I already have an account</a>
        <p class="mt-3 text-xs text-slate-500">Create your account first. You can complete the membership payment from your dashboard.</p>
      @elseif($user->hasActiveMembership())
        <div class="mt-6 rounded-xl bg-green-50 border border-green-100 p-4 text-sm text-green-800">
          <p class="font-semibold">Your membership is active.</p>
          @if($user->membership_paid_at)
            <p class="mt-1">Member since {{ $user->membership_paid_at->format('M j, Y') }}.</p>
          @endif
        </div>
        <a href="{{ route('dashboard') }}" class="mt-4 block w-full text-center px-5 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition {{ $focus }}">Go to your dashboard</a>
      @elseif($user->membershipIsPending())
        <div class="mt-6 rounded-xl bg-amber-50 border border-amber-100 p-4 text-sm text-amber-900">
          <p class="font-semibold">Payment submitted — awaiting confirmation.</p>
          <p class="mt-1">Reference: {{ $user->membership_reference ?: 'Not provided' }}</p>
          <p class="mt-2">The team will confirm your payment and email you. You do not need to pay again.</p>
        </div>
        <a href="{{ route('contact') }}" class="mt-4 block w-full text-center px-5 py-3 border border-slate-200 rounded-lg font-semibold hover:bg-slate-50 transition {{ $focus }}">Contact the team</a>
      @else
        <div class="mt-6 space-y-4">
          @if($paymentUrl)
            <a href="{{ $paymentUrl }}" target="_blank" rel="noopener noreferrer" class="block w-full text-center px-5 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition {{ $focus }}">Pay {{ $price }} securely</a>
          @endif

          @if($instructions)
            <div class="rounded-xl bg-slate-50 border border-slate-100 p-4 text-sm text-slate-700 whitespace-pre-line">{{ $instructions }}</div>
          @endif

          @unless($paymentUrl || $instructions)
            <div class="rounded-xl bg-slate-50 border border-slate-100 p-4 text-sm text-slate-700">
              Payment details are being set up. Please <a href="{{ route('contact') }}" class="text-[#FF5C3D] font-semibold hover:underline">contact the team</a> to complete your membership.
            </div>
          @endunless

          <form method="POST" action="{{ route('membership.payment.submit') }}" class="space-y-3 border-t border-slate-100 pt-4">
            @csrf
            <div>
              <label for="membership_reference" class="block text-sm font-medium text-slate-700 mb-1">Payment reference</label>
              <input id="membership_reference" name="membership_reference" value="{{ old('membership_reference') }}" required maxlength="120" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]" placeholder="Transaction reference or transfer note">
              @error('membership_reference')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <button class="w-full px-5 py-2.5 bg-[#101E33] text-white rounded-lg font-semibold hover:opacity-90 transition {{ $focus }}">I have paid</button>
            <p class="text-xs text-slate-500">Submitting a reference does not activate the membership automatically. The team verifies it first.</p>
          </form>
        </div>
      @endif
    </div>
  </div>
</section>

<div class="max-w-6xl mx-auto px-4 py-10 grid lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)] gap-8 items-start">
  <div class="space-y-6">
    <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
      <h2 class="text-2xl font-bold text-[#101E33]">What your membership includes</h2>
      <ul class="mt-5 grid sm:grid-cols-2 gap-3">
        @foreach($benefits as $benefit)
          <li class="flex items-start gap-3 text-sm text-slate-700">
            <svg class="w-5 h-5 shrink-0 text-[#FF5C3D]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
            <span>{{ $benefit }}</span>
          </li>
        @endforeach
      </ul>
    </section>

    <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
      <h2 class="text-2xl font-bold text-[#101E33]">How joining works</h2>
      <ol class="mt-5 space-y-4">
        @foreach([
          'Create your account and verify your email.',
          'Pay the one-time membership fee using the payment details on this page.',
          'Submit your payment reference so the team can confirm it.',
          'Receive your welcome email and onboarding checklist.',
        ] as $index => $step)
          <li class="flex items-start gap-4">
            <span class="w-8 h-8 shrink-0 rounded-full bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center font-bold">{{ $index + 1 }}</span>
            <p class="text-sm text-slate-700 pt-1">{{ $step }}</p>
          </li>
        @endforeach
      </ol>
    </section>

    @if($guarantee)
      <section class="bg-[#101E33] text-white rounded-2xl p-6 sm:p-8">
        <h2 class="text-xl font-bold">Our member promise</h2>
        <p class="mt-3 text-slate-300 leading-relaxed">{{ $guarantee }}</p>
      </section>
    @endif
  </div>

  <aside class="space-y-6">
    <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
      <h2 class="text-lg font-bold text-[#101E33]">Why members stay</h2>
      <ul class="mt-4 space-y-3 text-sm text-slate-600">
        <li><strong class="text-[#101E33]">One fee, no renewal.</strong> You are not buying a subscription that quietly renews.</li>
        <li><strong class="text-[#101E33]">A clear onboarding path.</strong> Your welcome email tells you exactly what to do first.</li>
        <li><strong class="text-[#101E33]">Real access.</strong> Events, chapters, mentorship, opportunities and speaker calls are all part of the membership.</li>
        <li><strong class="text-[#101E33]">A human confirms payments.</strong> If something goes wrong, you can reach the team.</li>
      </ul>
    </section>

    <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
      <h2 class="text-lg font-bold text-[#101E33]">Questions</h2>
      <dl class="mt-4 space-y-4 text-sm">
        <div>
          <dt class="font-semibold text-[#101E33]">Is this a monthly fee?</dt>
          <dd class="mt-1 text-slate-600">No. It is a one-time membership fee. There is no yearly renewal.</dd>
        </div>
        <div>
          <dt class="font-semibold text-[#101E33]">How long does confirmation take?</dt>
          <dd class="mt-1 text-slate-600">The team reviews payment references and emails you once confirmed. If it takes longer than expected, contact us.</dd>
        </div>
        <div>
          <dt class="font-semibold text-[#101E33]">What if it is not a fit?</dt>
          <dd class="mt-1 text-slate-600">{{ $guarantee ?: 'Contact the team and we will review your situation.' }}</dd>
        </div>
      </dl>
      <a href="{{ route('contact') }}" class="mt-5 inline-block px-4 py-2 border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 transition">Contact the team</a>
    </section>
  </aside>
</div>
@endsection