@extends('layouts.admin')
@section('content')
@php
  $p = $user->profile;
  $badge = ['active' => 'bg-green-50 text-green-700', 'suspended' => 'bg-red-50 text-red-700'];
  $membershipBadge = [
    'active' => 'bg-green-50 text-green-700',
    'pending' => 'bg-amber-50 text-amber-700',
    'unpaid' => 'bg-red-50 text-red-700',
    'exempt' => 'bg-[#101E33]/10 text-[#101E33]',
  ];
  $card = 'bg-white rounded-2xl p-6 border border-slate-100 shadow-sm';
  $h2 = 'text-lg font-bold text-[#101E33] mb-4';
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
  $link = 'text-[#FF5C3D] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $btn = 'inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-semibold border transition '.$focus;

  // Member-supplied URLs: only ever link http(s)
  $safeUrl = fn ($u) => is_string($u) && preg_match('#^https?://#i', trim($u)) ? trim($u) : null;
  $lines = fn ($t) => array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $t))));

  $isSelf = auth()->id() === $user->id;
  $isProtected = $isSelf || $user->isSuperAdmin();
  $name = e(addslashes($user->name));
  $role = $user->roles->first()?->name;
  $place = implode(', ', array_filter([$p?->city, $p?->state, $p?->country]));

  $socials = collect([
    'LinkedIn' => $p?->linkedin, 'GitHub' => $p?->github, 'Facebook' => $p?->facebook,
    'Twitter (X)' => $p?->twitter, 'TikTok' => $p?->tiktok,
  ])->map($safeUrl)->filter();
  $education = $lines($p?->education);
  $certifications = $lines($p?->certifications);
@endphp

<div class="max-w-5xl mx-auto px-4 py-8 pb-12">

  <a href="{{ route('admin.members.index') }}" class="text-sm {{ $link }}">Back to members</a>

  {{-- Header --}}
  <div class="mt-3 mb-6 flex flex-col md:flex-row md:items-start justify-between gap-4">
    <div class="flex items-start gap-4 min-w-0">
      @if($p?->avatar)
        <img src="{{ $p->avatar }}" alt="" class="w-16 h-16 shrink-0 rounded-full object-cover border border-slate-100">
      @else
        <div class="w-16 h-16 shrink-0 rounded-full bg-gradient-to-br from-[#FF5C3D] to-[#101E33] text-white flex items-center justify-center text-2xl font-bold" aria-hidden="true">{{ strtoupper(substr($user->name,0,1)) }}</div>
      @endif
      <div class="min-w-0">
        <h1 class="text-2xl font-bold text-[#101E33]">{{ $user->name }}@if($isSelf) <span class="text-sm font-normal text-slate-500">(you)</span>@endif</h1>
        <div class="mt-2 flex flex-wrap items-center gap-2">
          <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badge[$user->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($user->status) }}</span>
          <span class="px-2 py-1 rounded-full text-xs bg-slate-100 text-slate-700">{{ $role ? ucfirst(str_replace('_', ' ', $role)) : 'No role' }}</span>
          @if($p?->username)<span class="text-sm text-slate-600">&#64;{{ $p->username }}</span>@endif
        </div>
      </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 shrink-0">
      @if($p?->is_public && $p->username)
        <a href="{{ $p->publicUrl() }}" class="{{ $btn }} border-slate-200 text-slate-700 bg-white hover:bg-slate-50">View public profile</a>
      @endif

      @if($isProtected)
        <span class="text-sm text-slate-500">{{ $isSelf ? 'This is your account' : 'Super admin accounts cannot be suspended here' }}</span>
      @elseif($user->status === 'suspended')
        <form method="POST" action="{{ route('admin.members.restore', $user) }}" onsubmit="return confirm('Restore access for {{ $name }}?')">@csrf
          <button type="submit" class="{{ $btn }} border-green-600 bg-green-600 text-white hover:bg-green-700">Restore account</button></form>
      @else
        <form method="POST" action="{{ route('admin.members.suspend', $user) }}" onsubmit="return confirm('Suspend {{ $name }}?')">@csrf
          <button type="submit" class="{{ $btn }} border-red-300 text-red-700 bg-white hover:bg-red-50">Suspend account</button></form>
      @endif
    </div>
  </div>

  <div class="grid md:grid-cols-3 gap-6 items-start">

    {{-- Main --}}
    <div class="md:col-span-2 space-y-6">

      <section class="{{ $card }}" aria-labelledby="acct-h">
        <h2 id="acct-h" class="{{ $h2 }}">Account</h2>
        <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
          <div><dt class="text-slate-600">Email</dt><dd class="font-medium text-[#101E33] break-all"><a href="mailto:{{ $user->email }}" class="{{ $link }}">{{ $user->email }}</a></dd></div>
          <div><dt class="text-slate-600">Phone</dt><dd class="font-medium text-[#101E33]">
            @if($p?->phone)<a href="tel:{{ preg_replace('/[^\d+]/', '', $p->phone) }}" class="{{ $link }}">{{ $p->phone }}</a>@else Not set @endif
          </dd></div>
          <div><dt class="text-slate-600">Date of birth</dt><dd class="font-medium text-[#101E33]">{{ $p?->date_of_birth?->format('M j, Y') ?? 'Not set' }} <span class="text-xs font-normal text-slate-500">(private)</span></dd></div>
          <div>
            <dt class="text-slate-600">Joined</dt>
            <dd class="font-medium text-[#101E33]">
              @if($user->created_at){{ $user->created_at->format('M j, Y') }} <span class="font-normal text-slate-500">({{ $user->created_at->diffForHumans() }})</span>@else Unknown @endif
            </dd>
          </div>
          <div><dt class="text-slate-600">Location</dt><dd class="font-medium text-[#101E33]">{{ $place ?: 'Not set' }}</dd></div>
          <div><dt class="text-slate-600">Profile visibility</dt><dd class="font-medium text-[#101E33]">{{ $p ? ($p->is_public ? 'Public' : 'Private') : 'No profile yet' }}</dd></div>
          @if($user->chapters?->count())
            <div><dt class="text-slate-600">Chapters</dt><dd class="font-medium text-[#101E33]">{{ $user->chapters->pluck('name')->implode(', ') }}</dd></div>
          @endif
        </dl>
      </section>

      <section class="{{ $card }}" aria-labelledby="membership-h">
        <h2 id="membership-h" class="{{ $h2 }}">Membership</h2>
        <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
          <div>
            <dt class="text-slate-600">Status</dt>
            <dd class="mt-1">
              <span class="px-2 py-1 rounded-full text-xs font-medium {{ $membershipBadge[$user->membership_status] ?? 'bg-slate-100 text-slate-700' }}">
                {{ ['active' => 'Active', 'pending' => 'Pending confirmation', 'unpaid' => 'Unpaid', 'exempt' => 'Waived'][$user->membership_status] ?? ucfirst($user->membership_status) }}
              </span>
            </dd>
          </div>
          <div><dt class="text-slate-600">Reference</dt><dd class="font-medium text-[#101E33] break-all">{{ $user->membership_reference ?: 'Not provided' }}</dd></div>
          <div><dt class="text-slate-600">Confirmed</dt><dd class="font-medium text-[#101E33]">{{ $user->membership_paid_at?->format('M j, Y g:i A') ?? 'Not yet' }}</dd></div>
          <div><dt class="text-slate-600">Confirmed by</dt><dd class="font-medium text-[#101E33]">{{ $user->membership_confirmed_by ? (\App\Models\User::find($user->membership_confirmed_by)?->name ?? 'Staff') : '—' }}</dd></div>
          <div><dt class="text-slate-600">Member number</dt><dd class="font-medium text-[#101E33]">{{ $user->member_number ?: '—' }}</dd></div>
          <div><dt class="text-slate-600">Referral code</dt><dd class="font-mono font-medium text-[#101E33]">{{ $user->referral_code ?: '—' }}</dd></div>
          <div><dt class="text-slate-600">Referrals</dt><dd class="font-medium text-[#101E33]">{{ $user->referrals()->count() }}</dd></div>
          <div><dt class="text-slate-600">Founding member</dt><dd class="font-medium text-[#101E33]">{{ $user->is_founding_member ? 'Yes' : 'No' }}</dd></div>
        </dl>

        @if(! in_array($user->membership_status, ['active', 'exempt'], true))
          <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-100 pt-5">
            <form method="POST" action="{{ route('admin.members.membership.confirm', $user) }}" onsubmit="return confirm('Confirm membership payment for {{ $name }}?')">
              @csrf
              <button type="submit" class="{{ $btn }} border-green-600 bg-green-600 text-white hover:bg-green-700">Confirm payment</button>
            </form>
            <form method="POST" action="{{ route('admin.members.membership.waive', $user) }}" onsubmit="return confirm('Waive the membership fee for {{ $name }}?')">
              @csrf
              <button type="submit" class="{{ $btn }} border-slate-200 text-slate-700 bg-white hover:bg-slate-50">Waive fee</button>
            </form>
          </div>
          <p class="mt-3 text-xs text-slate-500">Confirming or waiving activates the account and emails the welcome guide.</p>
        @endif
      </section>

      @if($p)
        <section class="{{ $card }}" aria-labelledby="bio-h">
          <h2 id="bio-h" class="{{ $h2 }}">Bio</h2>
          @if($p->headline)<p class="text-sm font-medium text-[#101E33] mb-2">{{ $p->headline }}</p>@endif
          @if($p->bio)
            <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $p->bio }}</p>
          @else
            <p class="text-sm text-slate-500">No bio provided.</p>
          @endif
        </section>

        @if($education || $certifications)
          <section class="{{ $card }}" aria-labelledby="edu-h">
            <h2 id="edu-h" class="{{ $h2 }}">Education and certifications</h2>
            <div class="grid sm:grid-cols-2 gap-6 text-sm">
              @if($education)
                <div><h3 class="text-slate-600 mb-1">Education</h3>
                  <ul class="space-y-1 font-medium text-[#101E33]">@foreach($education as $line)<li>{{ $line }}</li>@endforeach</ul></div>
              @endif
              @if($certifications)
                <div><h3 class="text-slate-600 mb-1">Certifications</h3>
                  <ul class="space-y-1 font-medium text-[#101E33]">@foreach($certifications as $line)<li>{{ $line }}</li>@endforeach</ul></div>
              @endif
            </div>
          </section>
        @endif
      @else
        <section class="{{ $card }}">
          <p class="text-sm text-slate-500">This member has not created a profile yet.</p>
        </section>
      @endif

      @if($user->skills->count())
        <section class="{{ $card }}" aria-labelledby="skills-h">
          <h2 id="skills-h" class="{{ $h2 }}">Skills</h2>
          <ul class="flex flex-wrap gap-2">
            @foreach($user->skills as $skill)
              <li class="px-3 py-1 rounded-full bg-[#FF5C3D]/10 text-[#C23A1F] text-xs font-medium">{{ $skill->name }}</li>
            @endforeach
          </ul>
        </section>
      @endif
    </div>

    {{-- Sidebar --}}
    @if($p)
      <aside class="space-y-6" aria-label="Professional details">
        <section class="{{ $card }}" aria-labelledby="pro-h">
          <h2 id="pro-h" class="{{ $h2 }}">Professional</h2>
          <dl class="space-y-3 text-sm">
            @foreach([
              'Title' => $p->professional_title,
              'Technology category' => $p->technology_category,
              'Experience level' => $p->experience_level,
              'Organization' => $p->current_organization,
              'Availability' => $p->availability,
              'Languages' => $p->languages ? implode(', ', $lines($p->languages)) : null,
            ] as $label => $value)
              <div><dt class="text-slate-600">{{ $label }}</dt><dd class="font-medium text-[#101E33]">{{ $value ?: 'Not set' }}</dd></div>
            @endforeach
          </dl>
        </section>

        <section class="{{ $card }}" aria-labelledby="links-h">
          <h2 id="links-h" class="{{ $h2 }}">Links</h2>
          <ul class="space-y-2 text-sm">
            @forelse($socials as $label => $url)
              <li><a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="{{ $link }}">{{ $label }}<span class="sr-only"> (opens in a new tab)</span></a></li>
            @empty
              <li class="text-slate-500">No links shared.</li>
            @endforelse
          </ul>
        </section>
      </aside>
    @endif
  </div>
</div>
@endsection