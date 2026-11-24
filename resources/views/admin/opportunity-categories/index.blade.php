@extends('layouts.admin')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
  <div class="flex items-end justify-between gap-3 mb-6">
    <div>
      <a href="{{ route('admin.dashboard') }}" class="text-sm text-[#FF5C3D] hover:underline">← Admin dashboard</a>
      <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Opportunity categories</h1>
    </div>
    <a href="{{ route('admin.opportunity-categories.create') }}" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold">New category</a>
  </div>
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-slate-500"><tr><th class="text-left p-3">Name</th><th class="text-left p-3">Opportunities</th><th class="text-left p-3"></th></tr></thead>
      <tbody>
        @forelse($categories as $category)
          <tr class="border-t border-slate-100">
            <td class="p-3 font-medium text-[#101E33]">{{ $category->name }}</td>
            <td class="p-3 text-slate-600">{{ $category->opportunities_count }}</td>
            <td class="p-3">
              <a href="{{ route('admin.opportunity-categories.edit', $category) }}" class="text-[#FF5C3D] hover:underline">Edit</a>
              <form method="POST" action="{{ route('admin.opportunity-categories.destroy', $category) }}" class="inline ml-2" onsubmit="return confirm('Delete this category?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="3" class="p-6 text-center text-slate-500">No categories yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
