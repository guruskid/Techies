@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto mt-8 px-4">
  <h1 class="text-2xl font-bold mb-4 text-[#101E33]">Admin Dashboard</h1>
  <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
    @foreach($stats as $k=>$v)
      <div class="bg-white rounded-xl p-6 border shadow-sm"><div class="text-2xl font-bold text-[#FF5C3D]">{{ $v }}</div><div class="text-sm text-slate-500">{{ $k }}</div></div>
    @endforeach
  </div>
  <div class="flex gap-3">
    <a href="{{ route('admin.members.index') }}" class="px-4 py-2 bg-[#101E33] text-white rounded-lg">Manage Members</a>
    <a href="{{ route('admin.hubs.index') }}" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg">Manage Hubs</a>
  </div>
</div>
@endsection
