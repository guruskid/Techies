@extends('layouts.admin')
@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
@endphp
<div class="max-w-3xl mx-auto px-4 py-8 pb-12">
  <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div class="min-w-0">
      <a href="{{ route('admin.opportunities.index') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }} rounded">Back to opportunities</a>
      <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Edit opportunity</h1>
      <p class="mt-1 text-sm text-slate-600 break-words">{{ $opportunity->title }}</p>
    </div>
    {{-- Only published opportunities have a public page --}}
    @if($opportunity->status === 'published')
      <a href="{{ route('opportunities.show', $opportunity) }}" class="shrink-0 px-4 py-2 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 text-center hover:bg-slate-50 transition {{ $focus }}">View on site</a>
    @endif
  </div>

  @if($errors->any())
    <div class="mb-6 rounded-xl bg-red-50 border border-red-100 p-4 text-sm text-red-700" role="alert">
      <p class="font-medium mb-1">Fix {{ $errors->count() }} {{ \Illuminate\Support\Str::plural('problem', $errors->count()) }} and save again:</p>
      <ul class="list-disc list-inside">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
  @endif

  @include('admin.opportunities._form', ['opportunity' => $opportunity, 'categories' => $categories])
</div>
@endsection