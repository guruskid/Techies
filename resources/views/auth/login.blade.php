@extends('layouts.app')
@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-12 px-4">
  <div class="max-w-5xl w-full grid md:grid-cols-2 gap-0 bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">
    <div class="hidden md:flex flex-col justify-between bg-[#101E33] text-white p-10">
      <div>
        <div class="text-2xl font-bold">MWTT</div>
        <h2 class="mt-8 text-3xl font-bold leading-tight">Welcome back to the community.</h2>
        <p class="mt-4 text-slate-300">Connect with techies, discover hubs, join events and find opportunities.</p>
      </div>
      <div class="text-sm text-slate-400">© {{ date('Y') }} Meet with the Techies</div>
    </div>
    <div class="p-10">
      <h1 class="text-2xl font-bold text-[#101E33]">Login to your account</h1>
      <p class="mt-1 text-slate-500 text-sm">Enter your credentials to continue.</p>
      <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Email Address</label>
          <input name="email" type="email" class="w-full border border-slate-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]" required>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Password</label>
          <input name="password" type="password" class="w-full border border-slate-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]" required>
        </div>
        <div class="flex items-center justify-between text-sm">
          <label class="flex items-center gap-2 text-slate-500"><input type="checkbox" name="remember" class="rounded"> Remember me</label>
          <a href="{{ route('password.request') }}" class="text-[#FF5C3D] hover:underline">Forgot password?</a>
        </div>
        <button class="w-full px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Login</button>
      </form>
      <p class="mt-6 text-sm text-slate-500 text-center">Don't have an account? <a href="{{ route('register') }}" class="text-[#FF5C3D] font-medium hover:underline">Join the Community</a></p>
    </div>
  </div>
</div>
@endsection
