@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="mb-6">
    <a href="{{ route('super-admin.dashboard') }}" class="text-sm text-[#FF5C3D] hover:underline">← Super admin console</a>
    <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Roles &amp; permissions</h1>
    <p class="mt-1 text-sm text-slate-500">A read-only view of every role and the permissions it grants. Assign roles from the users screen.</p>
  </div>

  <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
    @foreach($roles as $role)
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="font-bold text-[#101E33]">{{ str_replace('_', ' ', ucfirst($role->name)) }}</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ $role->name }}</p>
          </div>
          <span class="px-2.5 py-1 rounded-full bg-slate-100 text-xs text-slate-600">{{ $role->users_count }} {{ \Illuminate\Support\Str::plural('user', $role->users_count) }}</span>
        </div>
        <div class="mt-4">
          @if($role->permissions->isEmpty())
            <p class="text-sm text-slate-500">No explicit permissions.</p>
          @else
            <div class="flex flex-wrap gap-1.5">
              @foreach($role->permissions as $permission)
                <span class="px-2 py-0.5 rounded-full bg-[#FF5C3D]/10 text-[#FF5C3D] text-xs">{{ $permission->name }}</span>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    @endforeach
  </div>
</div>
@endsection
