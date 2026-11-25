@php
  $user = auth()->user();
  $accountLinks = [
    ['Passport', route('passport.show'), request()->routeIs('passport.*')],
    ['Profile', route('profile.edit'), request()->routeIs('profile.*')],
    ['Membership', route('membership.show'), request()->routeIs('membership.*')],
    ['Saved items', route('bookmarks.index'), request()->routeIs('bookmarks.*')],
    ['Notifications', route('notifications.index'), request()->routeIs('notifications.*')],
  ];
  $careerLinks = [
    ['My applications', route('applications.index'), request()->routeIs('applications.*')],
    ['My articles', route('member.content.index'), request()->routeIs('member.content.*')],
    ['Mentorship', route('mentorship.index'), request()->routeIs('mentorship.*')],
    ['Cohorts', route('cohorts.index'), request()->routeIs('cohorts.*')],
    ['Showcase', route('projects.index'), request()->routeIs('projects.*')],
    ['Referrals', route('referrals.index'), request()->routeIs('referrals.*')],
    ['Benefits', route('benefits.index'), request()->routeIs('benefits.*')],
    ['Programmes', route('programs.index'), request()->routeIs('programs.*')],
  ];
  $communityLinks = [
    ['Lounge', route('lounge.index'), request()->routeIs('lounge.*')],
    ['Grants', route('grants.index'), request()->routeIs('grants.*')],
    ['Introductions', route('introductions.index'), request()->routeIs('introductions.*')],
  ];
  if ($user?->hub) {
    $communityLinks[] = ['My hub', route('hub.dashboard'), request()->routeIs('hub.dashboard')];
  }
  $groups = [
    'Account' => $accountLinks,
    'Career' => $careerLinks,
    'Community' => $communityLinks,
  ];
@endphp

<div class="member-workspace-nav border-b border-slate-200 bg-white">
  <nav x-data="{ openMenu: null }" @click.outside="openMenu = null" @keydown.escape.window="openMenu = null" class="max-w-7xl mx-auto px-4 flex items-center gap-2" aria-label="Member workspace">
    <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif
       class="member-workspace-link whitespace-nowrap border-b-2 px-3 py-3 text-sm transition {{ request()->routeIs('dashboard') ? 'border-[#FF5C3D] font-semibold text-[#C23A1F]' : 'border-transparent text-slate-600 hover:border-slate-300 hover:text-[#101E33]' }}">Dashboard</a>
    @foreach($groups as $group => $links)
      @php
        $groupActive = collect($links)->contains(fn ($link) => $link[2]);
        $menuKey = \Illuminate\Support\Str::slug($group);
      @endphp
      <div class="member-workspace-group relative">
        <button type="button" @click="openMenu = openMenu === '{{ $menuKey }}' ? null : '{{ $menuKey }}'" :aria-expanded="(openMenu === '{{ $menuKey }}').toString()" aria-controls="member-nav-{{ $menuKey }}" class="member-workspace-toggle member-workspace-link flex items-center gap-2 border-b-2 px-3 py-3 text-sm text-slate-600 transition hover:border-slate-300 hover:text-[#101E33] {{ $groupActive ? 'border-[#FF5C3D] font-semibold text-[#C23A1F]' : 'border-transparent' }}">
          {{ $group }}
          <svg class="h-3.5 w-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
        </button>
        <ul id="member-nav-{{ $menuKey }}" x-show="openMenu === '{{ $menuKey }}'" x-cloak x-transition class="member-workspace-menu absolute left-0 top-full z-40 mt-1 grid min-w-56 gap-1 rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
          @foreach($links as [$label, $url, $active])
            <li><a href="{{ $url }}" @if($active) aria-current="page" @endif class="block rounded-lg px-3 py-2 text-sm {{ $active ? 'bg-[#FF5C3D]/10 font-semibold text-[#C23A1F]' : 'text-slate-700 hover:bg-slate-50 hover:text-[#101E33]' }}">{{ $label }}</a></li>
          @endforeach
        </ul>
      </div>
    @endforeach
  </nav>
</div>
