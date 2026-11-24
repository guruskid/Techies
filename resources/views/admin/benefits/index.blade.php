@extends('layouts.admin')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-8 pb-12">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
    <div>
      <h1 class="font-display text-2xl font-extrabold text-[#101E33]">Member benefits</h1>
      <p class="mt-1 text-sm text-slate-500">Partner offers, tools and resources shown on the member benefits page.</p>
    </div>
    <a href="{{ route('admin.benefits.create') }}" class="rounded-lg bg-[#FF5C3D] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e94f32]">New benefit</a>
  </div>

  <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-slate-500">
        <tr>
          <th class="p-3 text-left">Benefit</th>
          <th class="p-3 text-left">Category</th>
          <th class="p-3 text-left">Offer</th>
          <th class="p-3 text-left">Redeemed</th>
          <th class="p-3 text-left">Status</th>
          <th class="p-3"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($benefits as $benefit)
          <tr class="border-t border-slate-100">
            <td class="p-3">
              <div class="font-medium text-[#101E33]">{{ $benefit->title }}</div>
              <div class="text-xs text-slate-500">{{ $benefit->provider }}</div>
            </td>
            <td class="p-3 text-slate-600">{{ $benefit->category }}</td>
            <td class="p-3 text-slate-600">{{ $benefit->discount_label ?: '—' }}</td>
            <td class="p-3 text-slate-600">{{ $benefit->redemptions_count }}</td>
            <td class="p-3">
              <span class="rounded-full px-2 py-0.5 text-xs {{ $benefit->is_active ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }}">{{ $benefit->is_active ? 'Active' : 'Hidden' }}</span>
            </td>
            <td class="p-3 whitespace-nowrap">
              <a href="{{ route('admin.benefits.edit', $benefit) }}" class="text-[#C23A1F] hover:underline">Edit</a>
              <form method="POST" action="{{ route('admin.benefits.destroy', $benefit) }}" class="ml-3 inline" onsubmit="return confirm('Delete this benefit?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="p-8 text-center text-slate-500">No benefits yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $benefits->links() }}</div>
</div>
@endsection