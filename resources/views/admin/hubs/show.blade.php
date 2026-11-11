@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-bold text-[#101E33] mb-4">{{ $hub->name }}</h1>
  <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-2 text-sm">
    <div><span class="text-slate-500">Owner:</span> {{ $hub->owner?->name }}</div>
    <div><span class="text-slate-500">Status:</span> {{ $hub->status }}</div>
    <div><span class="text-slate-500">City:</span> {{ $hub->city }}{{ $hub->state ? ', '.$hub->state : '' }}</div>
    <div><span class="text-slate-500">Email:</span> {{ $hub->email }}</div>
    <div><span class="text-slate-500">Website:</span> {{ $hub->website }}</div>
    <div><span class="text-slate-500">Description:</span> {{ $hub->description }}</div>
  </div>
  <a href="{{ route('admin.hubs.index') }}" class="inline-block mt-4 text-[#FF5C3D] hover:underline">← Back</a>
</div>
@endsection
