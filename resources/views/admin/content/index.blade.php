@extends('layouts.admin')
@section('content')
@php
  $statusStyles = [
    'draft' => 'bg-slate-100 text-slate-700',
    'pending' => 'bg-amber-50 text-amber-700',
    'approved' => 'bg-green-50 text-green-700',
    'rejected' => 'bg-red-50 text-red-700',
  ];
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-1';
  $field = 'border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $btn = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold border transition '.$focus;

  $curStatus = (string) request('status');
  $curApproval = (string) request('approval');

  // Summary cards double as one-click filters. [label, count, colour, filter params]
  $cards = [
    ['Pending',           $counts['pending'],             'text-amber-700', ['status' => 'pending']],
    ['Super admin only',  $counts['super_admin_pending'], 'text-[#101E33]', ['status' => 'pending', 'approval' => 'super_admin']],
    ['Approved',          $counts['approved'],            'text-green-700', ['status' => 'approved']],
    ['Changes requested', $counts['rejected'],            'text-red-700',   ['status' => 'rejected']],
    ['Drafts',            $counts['draft'],               'text-slate-700', ['status' => 'draft']],
  ];
  $keep = fn (array $params) => route('admin.content.index', array_filter($params + [
    'search' => request('search'), 'topic' => request('topic'),
  ]));
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
    <div>
      <a href="{{ route('admin.dashboard') }}" class="text-sm text-[#FF5C3D] hover:underline {{ $focus }} rounded">Back to admin dashboard</a>
      <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Content review queue</h1>
      <p class="mt-1 text-sm text-slate-500">Topics marked super admin review can only be approved by a super administrator.</p>
    </div>
    @if(auth()->user()->isSuperAdmin())
      <a href="{{ route('super-admin.dashboard') }}" class="px-4 py-2 bg-[#101E33] text-white rounded-lg text-sm text-center hover:opacity-90 transition {{ $focus }}">Super admin console</a>
    @endif
  </div>

  {{-- Summary cards, each one a filter --}}
  <nav class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6" aria-label="Filter by review status">
    @foreach($cards as [$label, $value, $colour, $params])
      @php
        $on = $curStatus === ($params['status'] ?? '') && $curApproval === ($params['approval'] ?? '');
      @endphp
      <a href="{{ $keep($params) }}" @if($on) aria-current="page" @endif
         class="block bg-white rounded-2xl p-5 border shadow-sm transition {{ $on ? 'border-[#101E33] ring-1 ring-[#101E33]' : 'border-slate-100 hover:border-slate-300' }} {{ $focus }}">
        <div class="text-xs text-slate-600">{{ $label }}</div>
        <div class="mt-1 text-2xl font-bold tabular-nums {{ $colour }}">{{ number_format((int) $value) }}</div>
      </a>
    @endforeach
  </nav>

  {{-- Search and secondary filters --}}
  <form method="GET" action="{{ route('admin.content.index') }}" role="search" class="mb-4 flex flex-wrap items-end gap-2">
    @if($curStatus)<input type="hidden" name="status" value="{{ $curStatus }}">@endif
    <div>
      <label for="search" class="block text-sm font-medium text-slate-700 mb-1">Search articles</label>
      <input id="search" type="search" name="search" value="{{ request('search') }}" placeholder="Title or author" class="{{ $field }} w-60 max-w-full">
    </div>
    <div>
      <label for="topic" class="block text-sm font-medium text-slate-700 mb-1">Topic</label>
      <select id="topic" name="topic" onchange="this.form.submit()" class="{{ $field }}">
        <option value="">All topics</option>
        @foreach($topics as $key => $topic)
          <option value="{{ $key }}" @selected(request('topic') === $key)>{{ $topic['label'] }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label for="approval" class="block text-sm font-medium text-slate-700 mb-1">Reviewer</label>
      <select id="approval" name="approval" onchange="this.form.submit()" class="{{ $field }}">
        <option value="">Any reviewer</option>
        <option value="admin" @selected($curApproval === 'admin')>Admin review</option>
        <option value="super_admin" @selected($curApproval === 'super_admin')>Super admin review</option>
      </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-[#FF5C3D] text-white rounded-lg text-sm font-medium hover:opacity-90 transition {{ $focus }}">Search</button>
    @if(request()->hasAny(['search', 'status', 'topic', 'approval']))
      <a href="{{ route('admin.content.index') }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm hover:bg-slate-50 {{ $focus }}">Clear all</a>
    @endif
  </form>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
    <table class="w-full min-w-[900px] text-sm">
      <caption class="sr-only">Articles with topic, reviewer, status, SEO score and last update</caption>
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th scope="col" class="text-left p-3 font-medium">Article</th>
          <th scope="col" class="text-left p-3 font-medium">Topic</th>
          <th scope="col" class="text-left p-3 font-medium">Reviewer</th>
          <th scope="col" class="text-left p-3 font-medium">Status</th>
          <th scope="col" class="text-left p-3 font-medium">SEO</th>
          <th scope="col" class="text-left p-3 font-medium">Updated</th>
          <th scope="col" class="text-left p-3 font-medium">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($articles as $article)
          @php
            $seo = (int) $article->seoScore();
            $seoColour = $seo >= 80 ? 'bg-green-50 text-green-700' : ($seo >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700');
            $isPending = $article->status === 'pending';
          @endphp
          <tr class="border-t border-slate-100 align-top hover:bg-slate-50/60">
            <td class="p-3">
              <a href="{{ route('admin.content.show', $article) }}" class="font-medium text-[#101E33] hover:text-[#FF5C3D] hover:underline {{ $focus }} rounded">{{ $article->title }}</a>
              <div class="text-xs text-slate-500 mt-0.5">By {{ $article->author?->name ?? 'Unknown author' }}</div>
            </td>
            <td class="p-3 text-slate-700">{{ $article->topicLabel() }}</td>
            <td class="p-3">
              <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $article->requiresSuperAdminApproval() ? 'bg-[#101E33] text-white' : 'bg-[#FF5C3D]/10 text-[#C23A1F]' }}">{{ $article->approvalLabel() }}</span>
            </td>
            <td class="p-3">
              <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusStyles[$article->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $article->statusLabel() }}</span>
            </td>
            <td class="p-3">
              <span class="px-2 py-0.5 rounded-full text-xs font-medium tabular-nums {{ $seoColour }}" title="SEO completeness score">{{ $seo }}%</span>
            </td>
            <td class="p-3 text-xs text-slate-700 whitespace-nowrap">
              @if($isPending && $article->submitted_at)
                <div class="font-medium">Submitted {{ $article->submitted_at->diffForHumans() }}</div>
                <div class="text-slate-500">Updated {{ $article->updated_at->diffForHumans() }}</div>
              @else
                {{ $article->updated_at->diffForHumans() }}
              @endif
            </td>
            <td class="p-3">
              <a href="{{ route('admin.content.show', $article) }}"
                 class="{{ $btn }} {{ $isPending ? 'border-[#101E33] bg-[#101E33] text-white hover:opacity-90' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                {{ $isPending ? 'Review' : 'Open' }}<span class="sr-only"> {{ $article->title }}</span>
              </a>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="p-8 text-center text-slate-500">
            No articles match your filters.
            @if(request()->hasAny(['search', 'status', 'topic', 'approval']))
              <a href="{{ route('admin.content.index') }}" class="text-[#FF5C3D] hover:underline">Clear filters</a>
            @endif
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <nav class="mt-4" aria-label="Pagination">{{ $articles->withQueryString()->links() }}</nav>
</div>
@endsection