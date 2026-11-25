@extends('layouts.app')
@section('title', 'Notifications · Meet with the Techies')
@section('content')
@include('partials.member-nav')
<main class="mx-auto max-w-5xl px-4 py-8">
  <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
      <p class="text-xs font-bold uppercase tracking-widest text-[#C23A1F]">Your updates</p>
      <h1 class="mt-2 font-display text-3xl font-extrabold text-[#101E33]">Notifications</h1>
      <p class="mt-2 text-sm text-slate-600">Event, application, mentorship, and community updates.</p>
    </div>
    <form method="POST" action="{{ route('notifications.read-all') }}">
      @csrf
      @method('PATCH')
      <button class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Mark all as read</button>
    </form>
  </div>
  <div class="space-y-3">
    @forelse($notifications as $notification)
      <article class="flex items-start justify-between gap-4 rounded-2xl border p-5 {{ $notification->read_at ? 'border-slate-200 bg-white' : 'border-[#FF5C3D]/30 bg-[#FF5C3D]/5' }}">
        <div>
          <h2 class="font-semibold text-[#101E33]"><a href="{{ route('notifications.open', $notification->id) }}" class="hover:text-[#C23A1F] hover:underline">{{ $notification->data['title'] ?? \Illuminate\Support\Str::headline(class_basename($notification->type)) }}</a></h2>
          <p class="mt-1 text-xs text-slate-500">{{ $notification->created_at->diffForHumans() }}</p>
        </div>
        @if(!$notification->read_at)
          <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
            @csrf
            @method('PATCH')
            <button class="whitespace-nowrap text-sm font-semibold text-[#C23A1F] hover:underline">Mark read</button>
          </form>
        @endif
      </article>
    @empty
      <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
        <h2 class="font-semibold text-[#101E33]">You’re all caught up</h2>
        <p class="mt-2 text-sm text-slate-600">New community updates will appear here.</p>
      </div>
    @endforelse
  </div>
  <div class="mt-6">{{ $notifications->links() }}</div>
</main>
@endsection
