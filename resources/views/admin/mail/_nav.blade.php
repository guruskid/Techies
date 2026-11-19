@php
  $mailUnread = \App\Models\EmailMessage::whereHas('account', fn ($account) => $account->visibleTo(auth()->user()))
    ->where('direction', \App\Models\EmailMessage::DIRECTION_INBOUND)
    ->whereNull('read_at')
    ->count();
  $mailTabs = [
    ['label' => 'Overview', 'route' => 'admin.mail.index', 'active' => request()->routeIs('admin.mail.index')],
    ['label' => 'Inbox', 'route' => 'admin.mail.inbox', 'active' => request()->routeIs('admin.mail.inbox', 'admin.mail.inbox.show'), 'badge' => $mailUnread],
    ['label' => 'Sent', 'route' => 'admin.mail.sent', 'active' => request()->routeIs('admin.mail.sent', 'admin.mail.sent.show')],
    ['label' => 'Compose', 'route' => 'admin.mail.compose', 'active' => request()->routeIs('admin.mail.compose')],
    ['label' => 'Broadcasts', 'route' => 'admin.mail.broadcasts.index', 'active' => request()->routeIs('admin.mail.broadcasts.*')],
    ['label' => 'Templates', 'route' => 'admin.mail.templates.index', 'active' => request()->routeIs('admin.mail.templates.*')],
    ['label' => 'Accounts', 'route' => 'admin.mail.accounts.index', 'active' => request()->routeIs('admin.mail.accounts.*')],
  ];
@endphp
<nav class="flex flex-wrap gap-2 mb-6" aria-label="Mail sections">
  @foreach($mailTabs as $tab)
    <a href="{{ route($tab['route']) }}" @if($tab['active']) aria-current="page" @endif
       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full text-sm border transition
              {{ $tab['active'] ? 'bg-[#101E33] border-[#101E33] text-white' : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300' }}
              focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
      {{ $tab['label'] }}
      @if(! empty($tab['badge']))
        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $tab['active'] ? 'bg-[#FF5C3D] text-white' : 'bg-[#FF5C3D]/10 text-[#FF5C3D]' }}">{{ $tab['badge'] }}</span>
      @endif
    </a>
  @endforeach
</nav>
