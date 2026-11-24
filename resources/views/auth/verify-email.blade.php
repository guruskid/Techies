@extends('layouts.app')
@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-12 px-4">
  <div class="max-w-5xl w-full grid md:grid-cols-2 gap-0 bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">
    <div class="hidden md:flex flex-col justify-between bg-[#101E33] text-white p-10">
      <div>
        <div class="text-2xl font-bold">MWTT</div>
        <h2 class="mt-8 text-3xl font-bold leading-tight">One more step to join the community.</h2>
        <p class="mt-4 text-slate-300">Verify your email address to activate your account and start connecting with techies, hubs and events.</p>
        <ul class="mt-6 space-y-2 text-sm text-slate-300">
          <li>✓ Secure your account</li>
          <li>✓ Receive important updates</li>
          <li>✓ Access your dashboard</li>
        </ul>
      </div>
      <div class="text-sm text-slate-400">© {{ date('Y') }} Meet with the Techies</div>
    </div>
    <div class="p-10 text-center">
      <div class="w-20 h-20 mx-auto rounded-full bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center text-4xl font-bold">@</div>
      <h1 class="mt-6 font-display text-2xl font-extrabold text-[#101E33]">Verify your email address</h1>
      <p class="mt-3 text-slate-500 text-sm leading-relaxed">We've sent a verification link to your email. Please check your inbox (and spam folder) and click the link to activate your account.</p>
      @if (session('status') == 'verification-link-sent')
        <div class="mt-4 p-3 rounded-lg bg-green-50 text-green-600 text-sm">A new verification link has been sent to your email.</div>
      @endif
      <form method="POST" action="{{ route('verification.send') }}" class="mt-8">
        @csrf
        <button class="w-full px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Resend Verification Email</button>
      </form>
      <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button class="text-sm text-slate-500 hover:text-[#FF5C3D] transition">Logout</button>
      </form>
    </div>
  </div>
</div>
@endsection
