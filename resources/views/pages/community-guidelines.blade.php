@extends('layouts.app')
@section('content')
@php
  $contactEmail = \App\Models\Setting::value('contact_email', config('techies.contact_email')) ?: 'hello@meettechies.ng';
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';

  // 'text' renders a paragraph, 'points' renders a bulleted list
  $sections = [
    ['our-purpose', 'Our Purpose', ['text' => 'MWTT exists to connect the technology ecosystem. These guidelines help ensure our community remains welcoming, respectful and useful for all members, hubs and chapters.']],
    ['be-respectful', 'Be Respectful', ['points' => [
      'Treat every member with dignity and respect.',
      'No harassment, bullying, hate speech, or discrimination of any kind.',
      'Disagree constructively. Attack ideas, not people.',
      'Use inclusive language.',
    ]]],
    ['be-authentic', 'Be Authentic', ['points' => [
      'Use your real name and identity.',
      'Do not impersonate others or create fake profiles.',
      'Keep your profile information accurate and up to date.',
    ]]],
    ['share-knowledge', 'Share Knowledge', ['points' => [
      'Share opportunities, insights and resources that help others grow.',
      'Give credit to original creators.',
      'Be helpful and constructive in discussions.',
    ]]],
    ['respect-privacy', 'Respect Privacy', ['points' => [
      "Do not share others' private information without consent.",
      'Respect profile visibility settings.',
      'Do not screenshot or redistribute private conversations.',
    ]]],
    ['no-spam', 'No Spam or Solicitation', ['points' => [
      'MWTT is not a marketplace. Avoid unsolicited promotions.',
      'Do not send bulk or repetitive messages.',
      'Promote events and opportunities through proper channels.',
    ]]],
    ['hub-chapter-conduct', 'Hub & Chapter Conduct', ['points' => [
      'Hub and chapter administrators must act in the best interest of their community.',
      'Publish accurate information about programs and events.',
      'Do not misuse member data.',
    ]]],
    ['safety', 'Safety', ['points' => [
      'Do not share harmful, illegal or explicit content.',
      'Report security vulnerabilities responsibly.',
      'Protect your account with a strong password.',
    ]]],
    ['reporting', 'Reporting & Enforcement', ['text' => 'If you see a violation, report it to the administrators. We review reports and may take action including warnings, content removal, suspension or permanent removal. Repeated or severe violations result in stricter action.']],
    ['changes', 'Changes', ['text' => 'These guidelines may evolve as the community grows. We will notify users of significant changes.']],
  ];
  $contactNumber = count($sections) + 1;
@endphp

<section class="relative overflow-hidden aurora text-white py-12 md:py-16">
  <div class="absolute inset-0 tech-grid opacity-25" aria-hidden="true"></div>
  <div class="relative max-w-7xl mx-auto px-4">
    <h1 class="font-display text-4xl font-bold">Community Guidelines</h1>
    <p class="mt-2 text-slate-300">How we keep MWTT welcoming, safe and valuable for everyone.</p>
  </div>
</section>

<div class="max-w-6xl mx-auto px-4 py-10 md:py-12 grid lg:grid-cols-4 gap-10 items-start">

  {{-- Table of contents: a sticky list on desktop, a collapsible one on mobile --}}
  <nav class="lg:col-span-1 lg:sticky lg:top-6" aria-label="On this page">
    <details class="lg:hidden rounded-xl border border-slate-200 bg-white p-4">
      <summary class="cursor-pointer text-sm font-semibold text-[#101E33] {{ $focus }} rounded">On this page</summary>
      <ol class="mt-3 space-y-2 text-sm">
        @foreach($sections as [$id, $title])
          <li><a href="#{{ $id }}" class="text-slate-700 hover:text-[#FF5C3D] hover:underline {{ $focus }} rounded">{{ $loop->iteration }}. {{ $title }}</a></li>
        @endforeach
        <li><a href="#contact" class="text-slate-700 hover:text-[#FF5C3D] hover:underline {{ $focus }} rounded">{{ $contactNumber }}. Contact</a></li>
      </ol>
    </details>

    <div class="hidden lg:block">
      <p class="text-sm font-semibold text-[#101E33] mb-3">On this page</p>
      <ol class="space-y-2 text-sm border-l border-slate-200 pl-4">
        @foreach($sections as [$id, $title])
          <li><a href="#{{ $id }}" class="text-slate-600 hover:text-[#FF5C3D] hover:underline {{ $focus }} rounded">{{ $loop->iteration }}. {{ $title }}</a></li>
        @endforeach
        <li><a href="#contact" class="text-slate-600 hover:text-[#FF5C3D] hover:underline {{ $focus }} rounded">{{ $contactNumber }}. Contact</a></li>
      </ol>
    </div>
  </nav>

  {{-- Guidelines --}}
  <div class="lg:col-span-3 space-y-10 text-slate-700 leading-relaxed max-w-3xl">
    @foreach($sections as [$id, $title, $body])
      <section id="{{ $id }}" aria-labelledby="{{ $id }}-h" class="scroll-mt-24">
        <h2 id="{{ $id }}-h" class="text-2xl font-bold text-[#101E33] mb-3">{{ $loop->iteration }}. {{ $title }}</h2>
        @isset($body['text'])
          <p>{{ $body['text'] }}</p>
        @endisset
        @isset($body['points'])
          <ul class="list-disc pl-6 space-y-1">
            @foreach($body['points'] as $point)<li>{{ $point }}</li>@endforeach
          </ul>
        @endisset
      </section>
    @endforeach

    <section id="contact" aria-labelledby="contact-h" class="scroll-mt-24">
      <h2 id="contact-h" class="text-2xl font-bold text-[#101E33] mb-3">{{ $contactNumber }}. Contact</h2>
      <p>Questions about these guidelines? Email <a href="mailto:{{ $contactEmail }}" class="text-[#FF5C3D] hover:underline {{ $focus }} rounded">{{ $contactEmail }}</a>, or use the <a href="{{ route('contact') }}" class="text-[#FF5C3D] hover:underline {{ $focus }} rounded">contact page</a>.</p>
    </section>
  </div>
</div>
@endsection