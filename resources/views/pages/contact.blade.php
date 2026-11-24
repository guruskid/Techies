@extends('layouts.app')
@section('content')
@php
  $contactEmail = \App\Models\Setting::value('contact_email', config('techies.contact_email'));
  $supportPhone = \App\Models\Setting::value('support_phone');

  $card = 'bg-white rounded-2xl border border-slate-100 shadow-sm';
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';
  $link = 'text-[#FF5C3D] font-medium hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] rounded';
  $labelClass = 'block text-sm font-medium text-slate-700 mb-1';
  $inputClass = 'w-full border border-slate-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $err = fn ($name) => $errors->has($name) ? '<p class="mt-1 text-xs text-red-600">'.e($errors->first($name)).'</p>' : '';

  // Social links come from config: only link http(s) URLs
  $socialNames = ['x' => 'X (Twitter)', 'linkedin' => 'LinkedIn', 'github' => 'GitHub', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'];
  $socials = collect(config('techies.social', []))
      ->filter(fn ($url) => is_string($url) && preg_match('#^https?://#i', trim($url)));

  // Flash message from the controller, if it sets one
  $sent = session('status') ?? session('success');
@endphp

<section class="relative overflow-hidden aurora text-white py-12 md:py-16">
  <div class="absolute inset-0 tech-grid opacity-25" aria-hidden="true"></div>
  <div class="absolute inset-0 opacity-20" aria-hidden="true">
    <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-[#FF5C3D] blur-3xl"></div>
  </div>
  <div class="max-w-7xl mx-auto px-4 relative">
    <h1 class="font-display text-4xl font-bold">Contact Us</h1>
    <p class="mt-3 text-slate-300 max-w-2xl">We'd love to hear from you. Reach out to the MWTT team for partnerships, membership, events or any questions.</p>
  </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-10 grid md:grid-cols-3 gap-8 items-start">

  {{-- Form first on mobile, second on desktop --}}
  <div class="md:col-span-2 md:order-2">
    <div class="{{ $card }} p-6 sm:p-8">
      <h2 class="text-2xl font-bold text-[#101E33] mb-2">Send a message</h2>
      <p class="text-slate-600 text-sm mb-6">Fill out the form and we'll get back to you as soon as possible.</p>

      @if($sent)
        <div class="mb-6 p-4 rounded-lg bg-green-50 text-green-800 text-sm" role="status">{{ $sent }}</div>
      @endif

      @if($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-red-50 text-red-700 text-sm" role="alert">
          <p class="font-medium mb-1">Fix {{ $errors->count() }} {{ \Illuminate\Support\Str::plural('problem', $errors->count()) }} and send again:</p>
          <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('contact.send') }}" class="space-y-5" onsubmit="this.querySelector('button[type=submit]').disabled = true">
        @csrf
        <div class="grid sm:grid-cols-2 gap-5">
          <div>
            <label for="contact-name" class="{{ $labelClass }}">Full name</label>
            <input id="contact-name" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required autocomplete="name" placeholder="Your name" class="{{ $inputClass }}">
            {!! $err('name') !!}
          </div>
          <div>
            <label for="contact-email" class="{{ $labelClass }}">Email address</label>
            <input id="contact-email" name="email" type="email" value="{{ old('email', auth()->user()->email ?? '') }}" required autocomplete="email" placeholder="you@example.com" class="{{ $inputClass }}">
            {!! $err('email') !!}
          </div>
        </div>
        <div>
          <label for="contact-subject" class="{{ $labelClass }}">Subject</label>
          <input id="contact-subject" name="subject" value="{{ old('subject') }}" required placeholder="What is this about?" class="{{ $inputClass }}">
          {!! $err('subject') !!}
        </div>
        <div>
          <label for="contact-message" class="{{ $labelClass }}">Message</label>
          <textarea id="contact-message" name="message" rows="6" required placeholder="Write your message..." class="{{ $inputClass }}">{{ old('message') }}</textarea>
          {!! $err('message') !!}
        </div>
        <button type="submit" class="w-full px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition disabled:opacity-60 disabled:cursor-not-allowed {{ $focus }}">Send message</button>
      </form>
    </div>
  </div>

  {{-- Contact details --}}
  <aside class="md:order-1 space-y-4" aria-label="Contact details">
    <div class="{{ $card }} p-6">
      <h2 class="font-bold text-[#101E33] mb-4">Reach us directly</h2>
      <ul class="space-y-5 text-sm">

        @if($contactEmail)
          <li class="flex gap-3">
            <span class="w-9 h-9 shrink-0 rounded-lg bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center" aria-hidden="true">
              <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path d="M2.5 5.5A1.5 1.5 0 014 4h12a1.5 1.5 0 011.5 1.5v.3l-7.5 4.5-7.5-4.5v-.3z"/><path d="M2.5 8.1V14.5A1.5 1.5 0 004 16h12a1.5 1.5 0 001.5-1.5V8.1l-7.1 4.3a.8.8 0 01-.8 0L2.5 8.1z"/></svg>
            </span>
            <div class="min-w-0">
              <p class="text-slate-600">Email</p>
              <a href="mailto:{{ $contactEmail }}" class="break-all {{ $link }}">{{ $contactEmail }}</a>
            </div>
          </li>
        @endif

        @if($supportPhone)
          <li class="flex gap-3">
            <span class="w-9 h-9 shrink-0 rounded-lg bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center" aria-hidden="true">
              <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3 4.5A1.5 1.5 0 014.5 3h2a1 1 0 01.95.68l.8 2.4a1 1 0 01-.3 1.07L6.6 8.4a11 11 0 005 5l1.25-1.35a1 1 0 011.07-.3l2.4.8a1 1 0 01.68.95v2A1.5 1.5 0 0115.5 17C8.6 17 3 11.4 3 4.5z"/></svg>
            </span>
            <div>
              <p class="text-slate-600">Phone</p>
              <a href="tel:{{ preg_replace('/[^0-9+]/', '', $supportPhone) }}" class="{{ $link }}">{{ $supportPhone }}</a>
            </div>
          </li>
        @endif

        <li class="flex gap-3">
          <span class="w-9 h-9 shrink-0 rounded-lg bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center" aria-hidden="true">
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18s6-5.2 6-10a6 6 0 10-12 0c0 4.8 6 10 6 10zm0-7.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" clip-rule="evenodd"/></svg>
          </span>
          <div>
            <p class="text-slate-600">Location</p>
            <p class="font-medium text-[#101E33]">Nigeria (National)</p>
            <p class="text-xs text-slate-500">Chapters across multiple states</p>
          </div>
        </li>

        <li class="flex gap-3">
          <span class="w-9 h-9 shrink-0 rounded-lg bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center" aria-hidden="true">
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-12.5a.75.75 0 00-1.5 0V10c0 .2.08.39.22.53l2.5 2.5a.75.75 0 101.06-1.06l-2.28-2.28V5.5z" clip-rule="evenodd"/></svg>
          </span>
          <div>
            <p class="text-slate-600">Office hours</p>
            <p class="font-medium text-[#101E33]">Mon to Fri, 9:00 AM to 5:00 PM (WAT)</p>
          </div>
        </li>
      </ul>
    </div>

    @if($socials->isNotEmpty())
      <div class="{{ $card }} p-6">
        <h2 class="font-bold text-[#101E33] mb-3">Follow us</h2>
        <ul class="flex flex-wrap gap-x-4 gap-y-2 text-sm">
          @foreach($socials as $platform => $url)
            <li><a href="{{ trim($url) }}" target="_blank" rel="noopener noreferrer" class="{{ $link }}">{{ $socialNames[$platform] ?? ucfirst($platform) }}<span class="sr-only"> (opens in a new tab)</span></a></li>
          @endforeach
        </ul>
      </div>
    @endif
  </aside>
</div>
@endsection