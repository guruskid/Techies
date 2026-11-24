@extends('layouts.admin')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
  <div class="mb-6">
    <a href="{{ route('super-admin.dashboard') }}" class="text-sm text-[#FF5C3D] hover:underline">← Super admin console</a>
    <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Organisation settings</h1>
    <p class="mt-1 text-sm text-slate-500">Public-facing defaults used across the website. Leave a field blank to use the built-in fallback.</p>
  </div>

  <form method="POST" action="{{ route('super-admin.settings.update') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-5">
    @csrf
    @foreach($fields as $key => $field)
      <div>
        <label for="{{ $key }}" class="block text-sm font-medium text-slate-700 mb-1">{{ $field['label'] }}</label>
        @if($field['type'] === 'textarea')
          <textarea id="{{ $key }}" name="{{ $key }}" rows="3" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">{{ old($key, $values->get($key, $field['default'])) }}</textarea>
        @else
          <input id="{{ $key }}" name="{{ $key }}" type="{{ $field['type'] }}" value="{{ old($key, $values->get($key, $field['default'])) }}" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
        @endif
        @error($key)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
      </div>
    @endforeach

    <div class="pt-2">
      <button class="px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Save settings</button>
    </div>
  </form>
</div>
@endsection
