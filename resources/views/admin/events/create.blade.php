@extends('layouts.admin')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-8 pb-12">
  <div class="mb-6">
    <a href="{{ route('admin.events.index') }}"
       class="text-sm text-[#FF5C3D] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded">Back to events</a>
    <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Create event</h1>
    <p class="mt-1 text-sm text-slate-500">Fill in the details below, then save.</p>
  </div>

  @if($errors->any())
    <div class="mb-6 rounded-xl bg-red-50 border border-red-100 p-4 text-sm text-red-700" role="alert">
      <p class="font-medium mb-1">Fix {{ $errors->count() }} {{ \Illuminate\Support\Str::plural('problem', $errors->count()) }} and save again:</p>
      <ul class="list-disc list-inside">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
  @endif

  @include('admin.events._form', ['event' => new \App\Models\Event, 'categories' => $categories])
</div>
@endsection