@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-8 pb-12">
  <div class="mb-6">
    <a href="{{ route('admin.speaker-calls.index') }}" class="text-sm text-[#FF5C3D] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded">Back to calls for speakers</a>
    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold text-[#101E33]">Edit call for speakers</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $speakerCall->title }}</p>
      </div>
      <a href="{{ route('admin.speaker-calls.submissions', $speakerCall) }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 transition">
        Review submissions ({{ $speakerCall->submissions()->count() }})
      </a>
    </div>
  </div>

  @if($errors->any())
    <div class="mb-6 rounded-xl bg-red-50 border border-red-100 p-4 text-sm text-red-700" role="alert">
      <p class="font-medium mb-1">Fix {{ $errors->count() }} {{ \Illuminate\Support\Str::plural('problem', $errors->count()) }} and save again:</p>
      <ul class="list-disc list-inside">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
  @endif

  @include('admin.speaker-calls._form', ['speakerCall' => $speakerCall, 'events' => $events, 'topicsText' => $topicsText])
</div>
@endsection