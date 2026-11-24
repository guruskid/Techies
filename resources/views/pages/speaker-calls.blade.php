@extends('layouts.app')
@section('title', 'Call for Speakers · Meet with the Techies')
@section('meta_description', 'Open calls for speakers from Meet with the Techies, hubs and chapters. Submit a talk topic and share your ideas with the community.')

@section('content')
@php
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
@endphp

<section class="bg-[#101E33] text-white py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-4">
    <p class="text-sm font-semibold uppercase tracking-wide text-[#FF5C3D]">Speak at an event</p>
    <h1 class="mt-2 text-3xl md:text-4xl font-bold">Call for speakers</h1>
    <p class="mt-3 max-w-2xl text-slate-300 text-lg">Browse open calls, choose a topic and submit a talk. Organisers review every proposal and email you the decision.</p>
  </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  @if($speakerCalls->count())
    <ul class="grid md:grid-cols-2 gap-5">
      @foreach($speakerCalls as $call)
        @php
          $source = $call->event?->title ?? $call->hub?->name ?? $call->chapter?->name ?? 'Meet with the Techies';
        @endphp
        <li>
          <a href="{{ route('speaker-calls.show', $call->slug) }}" class="group h-full flex flex-col bg-white rounded-2xl border border-slate-100 shadow-sm p-6 hover:border-[#FF5C3D] transition {{ $focus }}">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-xs font-medium text-[#C23A1F]">{{ $source }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#101E33] group-hover:underline">{{ $call->title }}</h2>
              </div>
              <span class="shrink-0 px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">Open</span>
            </div>

            @if($call->description)
              <p class="mt-3 text-sm text-slate-600 leading-relaxed">{{ \Illuminate\Support\Str::limit(strip_tags($call->description), 180) }}</p>
            @endif

            <div class="mt-4 flex flex-wrap gap-2">
              @foreach(array_slice($call->topicList(), 0, 3) as $topic)
                <span class="px-2.5 py-1 rounded-full text-xs bg-[#101E33]/5 text-[#101E33]">{{ $topic }}</span>
              @endforeach
              @if(count($call->topicList()) > 3)
                <span class="px-2.5 py-1 rounded-full text-xs bg-slate-100 text-slate-600">+{{ count($call->topicList()) - 3 }} more</span>
              @endif
            </div>

            <div class="mt-auto pt-5 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
              <span>{{ $call->closes_at ? 'Closes '.$call->closes_at->format('M j, Y') : 'No closing date' }}</span>
              <span>{{ $call->approved_submissions_count }} {{ \Illuminate\Support\Str::plural('speaker', $call->approved_submissions_count) }} confirmed</span>
            </div>
          </a>
        </li>
      @endforeach
    </ul>

    <div class="mt-6">{{ $speakerCalls->links() }}</div>
  @else
    <div class="max-w-2xl mx-auto text-center bg-white rounded-2xl border border-slate-100 p-10 shadow-sm">
      <h2 class="text-xl font-bold text-[#101E33]">No open calls right now</h2>
      <p class="mt-2 text-sm text-slate-500">Check back soon, or browse events and opportunities for other ways to get involved.</p>
      <a href="{{ route('events') }}" class="mt-5 inline-block px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Browse events</a>
    </div>
  @endif
</div>
@endsection