@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 pb-12">
  <a href="{{ route('admin.announcements.index') }}" class="text-sm text-[#FF5C3D] hover:underline">← Back to announcements</a>
  <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Edit announcement</h1>
  <p class="mt-1 mb-6 text-sm text-slate-500">Update the message or change whether it is published.</p>

  @include('admin.announcements._form')
</div>
@endsection
