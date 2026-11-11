@extends('layouts.app')
@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-12 px-4">
  <div class="max-w-md w-full bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100 p-10">
    <h1 class="text-2xl font-bold text-[#101E33]">Reset your password</h1>
    <p class="mt-1 text-slate-500 text-sm">Choose a new password for your account.</p>
    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Email Address</label>
        <input name="email" type="email" value="{{ $email ?? old('email') }}" class="w-full border border-slate-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]" required>
        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">New Password</label>
        <input name="password" type="password" class="w-full border border-slate-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]" required>
        @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Confirm Password</label>
        <input name="password_confirmation" type="password" class="w-full border border-slate-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]" required>
      </div>
      <button class="w-full px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Reset Password</button>
    </form>
  </div>
</div>
@endsection
