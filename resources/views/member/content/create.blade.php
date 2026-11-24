@extends('layouts.app')
@section('content')
@include('partials.member-nav')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="mb-6">
    <a href="{{ route('member.content.index') }}" class="text-sm text-[#FF5C3D] hover:underline">← Back to my content</a>
    <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Write a new article</h1>
    <p class="mt-1 text-slate-500">Draft your article, strengthen its SEO, then submit it for review.</p>
  </div>

  @if($errors->any())
    <div class="mb-6 rounded-xl bg-red-50 border border-red-100 p-4 text-sm text-red-700">
      <p class="font-semibold">Please fix the following:</p>
      <ul class="mt-1 list-disc list-inside">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @include('member.content.form')
</div>
@endsection
