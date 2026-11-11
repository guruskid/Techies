@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-bold text-[#101E33] mb-4">{{ $user->name }}</h1>
  <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-2 text-sm">
    <div><span class="text-slate-500">Email:</span> {{ $user->email }}</div>
    <div><span class="text-slate-500">Status:</span> {{ $user->status }}</div>
    <div><span class="text-slate-500">Title:</span> {{ $user->profile?->professional_title ?? '—' }}</div>
    <div><span class="text-slate-500">Location:</span> {{ $user->profile?->city }}{{ $user->profile?->state ? ', '.$user->profile?->state : '' }}</div>
  </div>
  <a href="{{ route('admin.members.index') }}" class="inline-block mt-4 text-[#FF5C3D] hover:underline">← Back</a>
</div>
@endsection
