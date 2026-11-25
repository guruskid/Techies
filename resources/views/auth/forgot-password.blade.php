@extends('layouts.app')
@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-12 px-4">
  <div class="max-w-5xl w-full grid md:grid-cols-2 gap-0 bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">
    <div class="auth-brand-panel hidden md:flex flex-col justify-between bg-[#101E33] text-white p-10">
      <div>
        <img src="{{ asset('images/logo-light.svg') }}" alt="Meet with the Techies" class="h-9 w-auto">
        <h2 class="mt-8 text-3xl font-bold leading-tight">Forgot your password?</h2>
        <p class="mt-4 text-slate-300">No worries — enter your email and we'll send you a link to reset it and get back into the community.</p>
      </div>
      <div class="text-sm text-slate-400">© {{ date('Y') }} Meet with the Techies</div>
    </div>
    <div class="p-10">
      <h1 class="font-display text-2xl font-extrabold text-[#101E33]">Reset your password</h1>
      <p class="mt-1 text-slate-500 text-sm">We'll email you a secure link to set a new password.</p>
      @if (session('status'))
        <div class="mt-4 p-3 rounded-lg bg-green-50 text-green-600 text-sm">{{ session('status') }}</div>
      @endif
      <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf
        <div>
          <label for="email" class="block text-xs font-medium text-slate-500 mb-1">Email address</label>
          <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" class="w-full border border-slate-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]" required>
          @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <button class="w-full px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Send Reset Link</button>
      </form>
      <p class="mt-6 text-sm text-slate-500 text-center"><a href="{{ route('login') }}" class="text-[#FF5C3D] font-medium hover:underline">Back to login</a></p>
    </div>
  </div>
</div>
@endsection
