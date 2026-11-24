@extends('layouts.admin')
@section('content')
<div class="max-w-md mx-auto px-4 py-8">
  <a href="{{ route('admin.opportunity-categories.index') }}" class="text-sm text-[#FF5C3D] hover:underline">← Back</a>
  <h1 class="mt-2 text-2xl font-bold text-[#101E33] mb-6">Edit opportunity category</h1>
  <form method="POST" action="{{ route('admin.opportunity-categories.update', $opportunityCategory) }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
    @csrf
    @method('PATCH')
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
      <input name="name" value="{{ old('name', $opportunityCategory->name) }}" required class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
      @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <button class="px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold">Update</button>
  </form>
</div>
@endsection
