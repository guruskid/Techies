@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto mt-8 px-4">
  <h1 class="text-2xl font-bold mb-4">{{ ucfirst(basename('hub/dashboard')) }} Dashboard</h1>
  @isset($stats)
  <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
    @foreach($stats as $k=>$v)
      <div class="bg-white rounded-xl p-6 border shadow-sm"><div class="text-2xl font-bold text-[#FF5C3D]">{{ $v }}</div><div class="text-sm text-slate-500">{{ $k }}</div></div>
    @endforeach
  </div>
  @endisset
</div>
@endsection
