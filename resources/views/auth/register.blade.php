@extends('layouts.app')
@section('content')
@php
  $labelClass = 'block text-sm font-medium text-slate-700 mb-1';
  $inputClass = 'w-full border border-slate-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
  $err = fn ($name) => $errors->has($name) ? '<p class="mt-1 text-xs text-red-600">'.e($errors->first($name)).'</p>' : '';
  $perks = ['Build your professional profile', 'Discover techies and hubs', 'Join events and opportunities', 'Grow with regional chapters'];
@endphp

<div class="min-h-[70vh] flex items-center justify-center py-10 md:py-12 px-4">
  <div class="max-w-5xl w-full grid md:grid-cols-2 gap-0 bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">

    {{-- Benefits panel --}}
    <div class="auth-brand-panel hidden md:flex flex-col justify-between bg-[#101E33] text-white p-10">
      <div>
        <img src="{{ asset('images/logo-light.svg') }}" alt="Meet with the Techies" class="h-9 w-auto">
        <h2 class="mt-8 text-3xl font-bold leading-tight">Join a growing tech community.</h2>
        <p class="mt-4 text-slate-300">Connect with developers, designers, founders, students and technology hubs across Nigeria.</p>
        <ul class="mt-6 space-y-3 text-sm text-slate-300">
          @foreach($perks as $perk)
            <li class="flex items-start gap-3">
              <svg class="w-5 h-5 shrink-0 text-[#FF5C3D]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
              <span>{{ $perk }}</span>
            </li>
          @endforeach
        </ul>
      </div>
      <div class="text-sm text-slate-300">&copy; {{ date('Y') }} Meet with the Techies</div>
    </div>

    {{-- Form --}}
    <div class="p-6 sm:p-10">
      <h1 class="font-display text-2xl font-extrabold text-[#101E33]">Create your account</h1>
      <p class="mt-1 text-slate-600 text-sm">Join MWTT in less than a minute.</p>
      <p class="mt-2 text-xs text-slate-500">Membership is a one-time fee. <a href="{{ route('membership.show') }}" class="text-[#FF5C3D] font-semibold hover:underline">See what is included</a>.</p>

      <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4"
            x-data="{ show: false }"
            onsubmit="this.querySelector('button[type=submit]').disabled = true">
        @csrf

        @if($errors->any())
          <div class="rounded-lg bg-red-50 border border-red-100 p-3 text-sm text-red-700" role="alert">
            <p class="font-medium mb-1">Fix {{ $errors->count() }} {{ \Illuminate\Support\Str::plural('problem', $errors->count()) }} and try again:</p>
            <ul class="list-disc list-inside">
              @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
          </div>
        @endif

        <div>
          <label for="name" class="{{ $labelClass }}">Full name</label>
          <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" autofocus required class="{{ $inputClass }}">
          {!! $err('name') !!}
        </div>

        <div>
          <label for="email" class="{{ $labelClass }}">Email address</label>
          <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="{{ $inputClass }}">
          {!! $err('email') !!}
        </div>

        <div>
          <label for="password" class="{{ $labelClass }}">Password</label>
          <div class="relative">
            <input id="password" name="password" :type="show ? 'text' : 'password'" type="password" autocomplete="new-password" required class="{{ $inputClass }} pr-16">
            <button type="button" @click="show = !show" :aria-pressed="show.toString()"
                    class="absolute inset-y-0 right-0 px-4 text-sm font-medium text-slate-600 hover:text-[#101E33] {{ $focus }} rounded-r-lg">
              <span x-text="show ? 'Hide' : 'Show'">Show</span><span class="sr-only"> password</span>
            </button>
          </div>
          {!! $err('password') !!}
        </div>

        <div>
          <label for="password_confirmation" class="{{ $labelClass }}">Confirm password</label>
          <input id="password_confirmation" name="password_confirmation" :type="show ? 'text' : 'password'" type="password" autocomplete="new-password" required class="{{ $inputClass }}">
        </div>

        <div>
          <label for="referral_code" class="{{ $labelClass }}">Referral code <span class="font-normal text-slate-500">(optional)</span></label>
          <input id="referral_code" name="referral_code" value="{{ old('referral_code', request('ref')) }}" maxlength="20" class="{{ $inputClass }}" placeholder="Enter a member's code">
          {!! $err('referral_code') !!}
        </div>

        <button type="submit" class="w-full px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition disabled:opacity-60 disabled:cursor-not-allowed {{ $focus }}">Create account</button>

        <p class="text-xs text-slate-600 text-center">
          By creating an account you agree to follow our
          <a href="{{ route('community_guidelines') }}" class="text-[#FF5C3D] hover:underline {{ $focus }} rounded">community guidelines</a>.
        </p>
      </form>

      <p class="mt-6 text-sm text-slate-600 text-center">Already have an account? <a href="{{ route('login') }}" class="text-[#FF5C3D] font-medium hover:underline {{ $focus }} rounded">Log in</a></p>
    </div>
  </div>
</div>
@endsection
