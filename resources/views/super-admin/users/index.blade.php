@extends('layouts.admin')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="mb-6">
    <a href="{{ route('super-admin.dashboard') }}" class="text-sm text-[#FF5C3D] hover:underline">← Super admin console</a>
    <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Users &amp; roles</h1>
    <p class="mt-1 text-sm text-slate-500">Assign roles, promote staff and suspend accounts. You cannot change your own role.</p>
  </div>

  <form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input name="search" value="{{ request('search') }}" placeholder="Search name or email" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
    <select name="role" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
      <option value="">All roles</option>
      @foreach($roles as $role)
        <option value="{{ $role }}" @selected(request('role') === $role)>{{ str_replace('_', ' ', ucfirst($role)) }}</option>
      @endforeach
    </select>
    <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
      <option value="">All statuses</option>
      <option value="active" @selected(request('status') === 'active')>Active</option>
      <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
    </select>
    <button class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm">Filter</button>
    @if(request()->hasAny(['search', 'role', 'status']))
      <a href="{{ route('super-admin.users.index') }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm">Clear</a>
    @endif
  </form>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-slate-500">
        <tr>
          <th class="text-left p-3">User</th>
          <th class="text-left p-3">Joined</th>
          <th class="text-left p-3">Status</th>
          <th class="text-left p-3">Role</th>
          <th class="text-left p-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $user)
          <tr class="border-t border-slate-100 align-middle">
            <td class="p-3">
              <div class="font-medium text-[#101E33]">{{ $user->name }} @if($user->id === auth()->id())<span class="text-xs text-slate-400">(you)</span>@endif</div>
              <div class="text-xs text-slate-500">{{ $user->email }}</div>
              @if($user->profile?->is_public)
                <a href="{{ $user->profile->publicUrl() }}" class="text-xs text-[#FF5C3D] hover:underline">View public profile</a>
              @endif
            </td>
            <td class="p-3 text-slate-500">{{ $user->created_at->format('M j, Y') }}</td>
            <td class="p-3">
              <span class="px-2 py-0.5 rounded-full text-xs {{ $user->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">{{ ucfirst($user->status) }}</span>
            </td>
            <td class="p-3">
              <form method="POST" action="{{ route('super-admin.users.role', $user) }}" class="flex items-center gap-2">
                @csrf
                <select name="role" class="border border-slate-200 rounded-lg px-2 py-1.5 text-xs" @disabled($user->id === auth()->id())>
                  @foreach($roles as $role)
                    <option value="{{ $role }}" @selected($user->hasRole($role))>{{ str_replace('_', ' ', ucfirst($role)) }}</option>
                  @endforeach
                </select>
                @if($user->id !== auth()->id())
                  <button class="text-xs text-[#FF5C3D] hover:underline">Save</button>
                @endif
              </form>
            </td>
            <td class="p-3">
              @if($user->id !== auth()->id() && ! $user->isSuperAdmin())
                @if($user->status === 'suspended')
                  <form method="POST" action="{{ route('super-admin.users.restore', $user) }}" class="inline">@csrf<button class="text-green-600 hover:underline">Restore</button></form>
                @else
                  <form method="POST" action="{{ route('super-admin.users.suspend', $user) }}" class="inline" onsubmit="return confirm('Suspend {{ addslashes($user->name) }}?')">@csrf<button class="text-red-600 hover:underline">Suspend</button></form>
                @endif
              @else
                <span class="text-xs text-slate-400">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="p-6 text-center text-slate-500">No users match your filters.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
