@extends('layouts.app')
@section('content')
@php
  $statusStyles = [
    'draft' => 'bg-slate-100 text-slate-600',
    'pending' => 'bg-amber-50 text-amber-700',
    'approved' => 'bg-green-50 text-green-700',
    'rejected' => 'bg-red-50 text-red-700',
  ];
@endphp
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
    <div>
      <a href="{{ route('admin.dashboard') }}" class="text-sm text-[#FF5C3D] hover:underline">← Admin dashboard</a>
      <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Content review queue</h1>
      <p class="mt-1 text-sm text-slate-500">Topics marked super admin review can only be approved by a super administrator.</p>
    </div>
    @if(auth()->user()->isSuperAdmin())
      <a href="{{ route('super-admin.dashboard') }}" class="px-4 py-2 bg-[#101E33] text-white rounded-lg text-sm">Super admin console</a>
    @endif
  </div>

  <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
    @foreach([
      ['Pending', $counts['pending'], 'text-amber-600'],
      ['Super admin only', $counts['super_admin_pending'], 'text-[#101E33]'],
      ['Approved', $counts['approved'], 'text-green-600'],
      ['Changes requested', $counts['rejected'], 'text-red-600'],
      ['Drafts', $counts['draft'], 'text-slate-600'],
    ] as [$label, $value, $colour])
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="text-xs text-slate-500">{{ $label }}</div>
        <div class="mt-1 text-2xl font-bold {{ $colour }}">{{ $value }}</div>
      </div>
    @endforeach
  </div>

  <form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input name="search" value="{{ request('search') }}" placeholder="Search title or author" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
    <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
      <option value="">All statuses</option>
      @foreach(\App\Models\Article::statuses() as $key => $label)
        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
      @endforeach
    </select>
    <select name="topic" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
      <option value="">All topics</option>
      @foreach($topics as $key => $topic)
        <option value="{{ $key }}" @selected(request('topic') === $key)>{{ $topic['label'] }}</option>
      @endforeach
    </select>
    <select name="approval" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
      <option value="">Any reviewer</option>
      <option value="admin" @selected(request('approval') === 'admin')>Admin review</option>
      <option value="super_admin" @selected(request('approval') === 'super_admin')>Super admin review</option>
    </select>
    <button class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm">Filter</button>
    @if(request()->hasAny(['search', 'status', 'topic', 'approval']))
      <a href="{{ route('admin.content.index') }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm">Clear</a>
    @endif
  </form>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-slate-500">
        <tr>
          <th class="text-left p-3">Article</th>
          <th class="text-left p-3">Topic</th>
          <th class="text-left p-3">Reviewer</th>
          <th class="text-left p-3">Status</th>
          <th class="text-left p-3">SEO</th>
          <th class="text-left p-3">Updated</th>
          <th class="text-left p-3"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($articles as $article)
          <tr class="border-t border-slate-100 align-top">
            <td class="p-3">
              <a href="{{ route('admin.content.show', $article) }}" class="font-medium text-[#101E33] hover:text-[#FF5C3D]">{{ $article->title }}</a>
              <div class="text-xs text-slate-500 mt-0.5">{{ $article->author?->name }}</div>
            </td>
            <td class="p-3">{{ $article->topicLabel() }}</td>
            <td class="p-3">
              <span class="px-2 py-0.5 rounded-full text-xs {{ $article->requiresSuperAdminApproval() ? 'bg-[#101E33] text-white' : 'bg-[#FF5C3D]/10 text-[#FF5C3D]' }}">{{ $article->approvalLabel() }}</span>
            </td>
            <td class="p-3">
              <span class="px-2 py-0.5 rounded-full text-xs {{ $statusStyles[$article->status] ?? 'bg-slate-100 text-slate-600' }}">{{ $article->statusLabel() }}</span>
            </td>
            <td class="p-3">{{ $article->seoScore() }}%</td>
            <td class="p-3 text-slate-500">{{ $article->updated_at->diffForHumans() }}</td>
            <td class="p-3">
              <a href="{{ route('admin.content.show', $article) }}" class="text-[#FF5C3D] hover:underline">Review</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="p-6 text-center text-slate-500">No articles match your filters.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $articles->links() }}</div>
</div>
@endsection
