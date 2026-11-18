@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
  <div class="mb-6">
    <a href="{{ route('admin.opportunities.index') }}" class="text-sm text-[#FF5C3D] hover:underline">← Opportunities</a>
    <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Edit opportunity</h1>
    <p class="mt-1 text-sm text-slate-500">{{ $opportunity->title }}</p>
  </div>
  @if($errors->any())
    <div class="mb-6 rounded-xl bg-red-50 border border-red-100 p-4 text-sm text-red-700">
      <ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
  @endif
  @include('admin.opportunities._form', ['opportunity' => $opportunity, 'categories' => $categories])
</div>
@endsection
