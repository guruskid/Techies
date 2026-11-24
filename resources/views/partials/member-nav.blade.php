@php
  $user = auth()->user();
  $tabs = [
    ['Dashboard', route('dashboard'), request()->routeIs('dashboard')],
    ['Profile', route('profile.edit'), request()->routeIs('profile.*')],
    ['My articles', route('member.content.index'), request()->routeIs('member.content.*')],
    ['Membership', route('membership.show'), request()->routeIs('membership.*')],
  ];
  if ($user?->hub) {
    $tabs[] = ['My hub', route('hub.dashboard'), request()->routeIs('hub.dashboard')];
    $tabs[] = ['Team', route('hub.team'), request()->routeIs('hub.team')];
  }
@endphp

<div class="border-b border-slate-200 bg-white">
  <nav class="max-w-7xl mx-auto px-4 flex gap-1 overflow-x-auto" aria-label="Member workspace">
    @foreach($tabs as [$label, $url, $active])
      <a href="{{ $url }}" @if($active) aria-current="page" @endif
         class="whitespace-nowrap border-b-2 px-3 py-3 text-sm transition {{ $active ? 'border-[#FF5C3D] font-semibold text-[#C23A1F]' : 'border-transparent text-slate-600 hover:border-slate-300 hover:text-[#101E33]' }}">{{ $label }}</a>
    @endforeach
  </nav>
</div>
