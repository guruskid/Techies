@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-bold text-[#101E33] mb-6">Members Management</h1>
  <form method="GET" class="mb-4 flex gap-2">
    <input name="search" placeholder="Search name/email" class="border border-slate-200 rounded-lg px-3 py-2">
    <select name="status" class="border border-slate-200 rounded-lg px-3 py-2">
      <option value="">All</option>
      <option value="active">Active</option>
      <option value="suspended">Suspended</option>
    </select>
    <button class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg">Filter</button>
  </form>
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-slate-500">
        <tr><th class="text-left p-3">Name</th><th class="text-left p-3">Email</th><th class="text-left p-3">Status</th><th class="text-left p-3">Actions</th></tr>
      </thead>
      <tbody>
        @foreach($members as $m)
        <tr class="border-t border-slate-100">
          <td class="p-3"><a href="{{ route('admin.members.show', $m) }}" class="text-[#FF5C3D] hover:underline">{{ $m->name }}</a></td>
          <td class="p-3">{{ $m->email }}</td>
          <td class="p-3">{{ $m->status }}</td>
          <td class="p-3">
            @if($m->status === 'suspended')
              <form method="POST" action="{{ route('admin.members.restore', $m) }}" class="inline">@csrf<button class="text-green-600 hover:underline">Restore</button></form>
            @else
              <form method="POST" action="{{ route('admin.members.suspend', $m) }}" class="inline">@csrf<button class="text-red-600 hover:underline">Suspend</button></form>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $members->links() }}</div>
</div>
@endsection
