@extends('layouts.app')
@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $btn = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold border transition '.$focus;
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
      <a href="{{ route('admin.mail.index') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }}">Back to mail center</a>
      <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Email templates</h1>
      <p class="mt-1 text-sm text-slate-500">Reusable subjects and bodies for direct mail and broadcasts.</p>
    </div>
    <a href="{{ route('admin.mail.templates.create') }}" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-semibold hover:opacity-90 transition text-center {{ $focus }}">New template</a>
  </div>

  @include('admin.mail._nav')

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
    <table class="w-full min-w-[720px] text-sm">
      <caption class="sr-only">Saved email templates</caption>
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th scope="col" class="text-left p-3 font-medium">Template</th>
          <th scope="col" class="text-left p-3 font-medium">Subject</th>
          <th scope="col" class="text-left p-3 font-medium">Updated</th>
          <th scope="col" class="text-left p-3 font-medium">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($templates as $template)
          <tr class="border-t border-slate-100 hover:bg-slate-50/60">
            <td class="p-3">
              <a href="{{ route('admin.mail.templates.edit', $template) }}" class="font-medium text-[#101E33] hover:text-[#FF5C3D] hover:underline {{ $focus }}">{{ $template->name }}</a>
              <div class="text-xs text-slate-500">{{ $template->creator?->name ? 'By '.$template->creator->name : '' }}</div>
            </td>
            <td class="p-3 text-slate-700">{{ $template->subject }}</td>
            <td class="p-3 text-slate-600 whitespace-nowrap">{{ $template->updated_at?->format('M j, Y') }}</td>
            <td class="p-3">
              <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.mail.templates.edit', $template) }}" class="{{ $btn }} border-[#101E33] bg-[#101E33] text-white hover:opacity-90">Edit</a>
                <form method="POST" action="{{ route('admin.mail.templates.destroy', $template) }}" onsubmit="return confirm('Delete this template?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="{{ $btn }} border-red-300 text-red-700 bg-white hover:bg-red-50">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="p-8 text-center text-slate-500">No templates yet. <a href="{{ route('admin.mail.templates.create') }}" class="text-[#FF5C3D] hover:underline">Create the first template</a>.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <nav class="mt-4" aria-label="Pagination">{{ $templates->links() }}</nav>
</div>
@endsection
