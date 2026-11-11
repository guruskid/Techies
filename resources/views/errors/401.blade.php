@extends('layouts.app')
@section('content')
<div class="min-h-[60vh] flex items-center justify-center py-16 px-4">
  <div class="text-center max-w-md">
    <div class="text-8xl font-black text-[#FF5C3D] tracking-tight">{{ 401 }}</div>
    <h1 class="mt-4 text-2xl font-bold text-[#101E33]">Unauthorized</h1>
    <p class="mt-2 text-slate-500">You need to log in to access this page.</p>
    <div class="mt-6 flex justify-center gap-3">
      <a href="/" class="px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Go Home</a>
      <a href="javascript:history.back()" class="px-6 py-3 border border-slate-300 rounded-lg font-medium hover:bg-slate-50 transition">Go Back</a>
    </div>
  </div>
</div>
@endsection
