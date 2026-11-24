@extends('layouts.admin')
@section('content')
@php
  $isSuperAdmin = auth()->user()->isSuperAdmin();

  // Optional: pass $counts from the controller, e.g. ['hubs' => 3, 'content' => 5].
  // A badge shows beside a card only when its count is above zero.
  $counts = $counts ?? [];

  $groups = [
    [
      'title' => 'People and places',
      'items' => [
        ['key' => 'members',  'title' => 'Members',  'description' => 'View, suspend and restore member accounts.',     'url' => route('admin.members.index')],
        ['key' => 'hubs',     'title' => 'Hubs',     'description' => 'Review, approve and manage technology hubs.',    'url' => route('admin.hubs.index'), 'badge' => 'pending'],
        ['key' => 'chapters', 'title' => 'Chapters', 'description' => 'Create and manage regional chapters.',           'url' => route('admin.chapters.index')],
        ['key' => 'mentorship', 'title' => 'Mentorship', 'description' => 'Match mentors and mentees, and close cycles.', 'url' => route('admin.mentorship.index')],
      ],
    ],
    [
      'title' => 'Community content',
      'items' => [
        ['key' => 'content',       'title' => 'Content review',  'description' => 'Review and approve member articles.',          'url' => route('admin.content.index'), 'badge' => 'to review'],
        ['key' => 'write',         'title' => 'Write an article', 'description' => 'Create and submit an article as an admin or super admin.', 'url' => route('member.content.create')],
        ['key' => 'my-articles',   'title' => 'My articles',     'description' => 'Drafts and submissions authored by you.',      'url' => route('member.content.index')],
        ['key' => 'announcements', 'title' => 'Announcements',   'description' => 'Publish community updates and news.',          'url' => route('admin.announcements.index')],
        ['key' => 'events',        'title' => 'Events',          'description' => 'Create and manage community events.',          'url' => route('admin.events.index')],
        ['key' => 'opportunities', 'title' => 'Opportunities',   'description' => 'Post and manage jobs and opportunities.',      'url' => route('admin.opportunities.index')],
        ['key' => 'speaker-calls', 'title' => 'Call for speakers', 'description' => 'Open calls, review proposals and email speakers.', 'url' => route('admin.speaker-calls.index')],
        ['key' => 'categories',    'title' => 'Categories',      'description' => 'Manage event and opportunity categories.',     'url' => route('admin.categories.index')],
        ['key' => 'benefits',      'title' => 'Member benefits',  'description' => 'Manage partner offers and member perks.',      'url' => route('admin.benefits.index')],
        ['key' => 'certificates',  'title' => 'Certificates',      'description' => 'Issue verified certificates to members.',      'url' => route('admin.certificates.index')],
        ['key' => 'feedback',      'title' => 'Member feedback',   'description' => 'Read thirty-day feedback from members.',       'url' => route('admin.feedback.index')],
      ],
    ],
    [
      'title' => 'Communication',
      'items' => [
        ['key' => 'mail', 'title' => 'Mail center', 'description' => 'Connect mailboxes, send and receive email, manage templates and broadcasts.', 'url' => route('admin.mail.index')],
      ],
    ],
  ];

  if ($isSuperAdmin) {
    $groups[] = [
      'title' => 'Super admin',
      'items' => [
        ['key' => 'users',    'title' => 'Users and roles', 'description' => 'Manage staff accounts, roles and permissions.', 'url' => route('super-admin.users.index'),    'dark' => true],
        ['key' => 'settings', 'title' => 'Settings',        'description' => 'Organisation settings and audit logs.',         'url' => route('super-admin.settings.edit'), 'dark' => true],
      ],
    ];
  }
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 pb-12">

  <div class="mb-8">
    <a href="{{ route('admin.dashboard') }}"
       class="text-sm text-[#FF5C3D] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded">
      Back to admin dashboard
    </a>
    <h1 class="mt-2 text-2xl font-bold text-[#101E33]">Management center</h1>
    <p class="mt-1 text-sm text-slate-500">Choose an area to manage.</p>
  </div>

  <div class="space-y-10">
    @foreach($groups as $group)
      <section aria-labelledby="group-{{ \Illuminate\Support\Str::slug($group['title']) }}">
        <h2 id="group-{{ \Illuminate\Support\Str::slug($group['title']) }}"
            class="text-sm font-bold text-[#101E33] mb-3">
          {{ $group['title'] }}
        </h2>

        <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          @foreach($group['items'] as $item)
            @php
              $count = (int) ($counts[$item['key']] ?? 0);
              $dark  = $item['dark'] ?? false;
            @endphp
            <li>
              <a href="{{ $item['url'] }}"
                 class="group h-full flex flex-col bg-white rounded-2xl border border-slate-100 shadow-sm p-5 {{ $dark ? 'hover:border-[#101E33]' : 'hover:border-[#FF5C3D]' }} focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
                <div class="flex items-start justify-between gap-3">
                  <h3 class="font-bold text-[#101E33] group-hover:underline">{{ $item['title'] }}</h3>
                  @if($count > 0 && isset($item['badge']))
                    <span class="shrink-0 px-2 py-0.5 rounded-full text-xs font-medium bg-[#FF5C3D]/10 text-[#FF5C3D]">
                      {{ $count }} {{ $item['badge'] }}
                    </span>
                  @endif
                </div>
                <p class="mt-2 text-sm text-slate-500">{{ $item['description'] }}</p>
              </a>
            </li>
          @endforeach
        </ul>
      </section>
    @endforeach
  </div>
</div>
@endsection